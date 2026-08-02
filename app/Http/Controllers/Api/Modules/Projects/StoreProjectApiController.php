<?php

namespace App\Http\Controllers\Api\Modules\Projects;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Jobs\ProcessProjectDocumentUpload;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoreProjectApiController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly ProjectWorkflowService $workflow) {}

    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'description' => ['nullable', 'string'],
            'document' => ['required', 'file', 'mimes:pdf,docx,doc,png,jpg,jpeg', 'max:10240'],
            'submit_action' => ['required', 'in:draft,submit'],
        ]);

        try {
            $project = DB::transaction(function () use ($validated, $request, $user) {
                $projectNum = 'PRJ-'.date('Ym').'-'.str_pad((Project::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
                $status = ($validated['submit_action'] === 'submit') ? Project::STATUS_SUBMITTED : Project::STATUS_DRAFT;

                $project = Project::create([
                    'project_number' => $projectNum,
                    'title' => $validated['title'],
                    'applicant_id' => $user->id,
                    'document_type_id' => $validated['document_type_id'],
                    'status' => $status,
                    'description' => $validated['description'] ?? null,
                    'submitted_at' => ($status === Project::STATUS_SUBMITTED) ? now() : null,
                ]);

                $file = $request->file('document');
                $filename = time().'_'.preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
                $temporaryPath = $file->storeAs(
                    'queued_project_documents/'.$project->id,
                    uniqid('upload_', true).'_'.$filename,
                    'local',
                );

                ProcessProjectDocumentUpload::dispatch(
                    projectId: $project->id,
                    uploadedBy: $user->id,
                    temporaryPath: $temporaryPath,
                    documentName: $file->getClientOriginalName(),
                    fileName: $filename,
                    fileSize: $file->getSize(),
                    mimeType: $file->getClientMimeType(),
                    version: 1,
                )->afterCommit();

                $this->workflow->logSubmission(
                    project: $project,
                    actor: $user,
                    action: ($status === Project::STATUS_SUBMITTED) ? 'submit' : 'create_draft',
                    previousStatus: null,
                    notes: 'Pengajuan dokumen kelayakan melalui API.',
                );

                return $project;
            });

            return $this->success(
                data: new ProjectResource($project->load(['documentType', 'applicant', 'documents'])),
                message: 'Permohonan dokumen berhasil disimpan. Berkas masuk antrean upload.',
                code: 201,
            );
        } catch (\Throwable $e) {
            Log::error('Failed to store API project.', ['error' => $e->getMessage()]);

            return $this->error('Gagal menyimpan permohonan.', 500);
        }
    }
}
