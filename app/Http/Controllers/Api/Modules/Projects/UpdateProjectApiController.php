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

class UpdateProjectApiController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly ProjectWorkflowService $workflow) {}

    public function __invoke(Request $request, int $id)
    {
        /** @var User $user */
        $user = $request->user();
        $project = Project::findOrFail($id);

        abort_unless($project->applicant_id === $user->id || $user->hasRole('admin'), 403);
        abort_unless(in_array($project->status, [Project::STATUS_DRAFT, Project::STATUS_REVISION], true), 422, 'Permohonan dokumen tidak dapat diubah.');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'description' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'mimes:pdf,docx,doc,png,jpg,jpeg', 'max:10240'],
            'submit_action' => ['required', 'in:draft,submit'],
        ]);

        try {
            DB::transaction(function () use ($project, $validated, $request, $user) {
                $previousStatus = $project->status;
                $newStatus = ($validated['submit_action'] === 'submit') ? Project::STATUS_SUBMITTED : $previousStatus;

                $project->update([
                    'title' => $validated['title'],
                    'document_type_id' => $validated['document_type_id'],
                    'description' => $validated['description'] ?? null,
                    'status' => $newStatus,
                    'submitted_at' => ($newStatus === Project::STATUS_SUBMITTED) ? now() : $project->submitted_at,
                    'approved_at' => null,
                    'rejected_at' => null,
                ]);

                if ($request->hasFile('document')) {
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
                    )->afterCommit();
                }

                $action = ($previousStatus === Project::STATUS_REVISION && $newStatus === Project::STATUS_SUBMITTED)
                    ? 'resubmit'
                    : (($newStatus === Project::STATUS_SUBMITTED) ? 'submit' : 'update_draft');

                $this->workflow->logSubmission(
                    project: $project,
                    actor: $user,
                    action: $action,
                    previousStatus: $previousStatus,
                    notes: ($action === 'resubmit')
                        ? 'Pemohon telah mengunggah perbaikan dokumen dan mengirimkan ulang permohonan.'
                        : 'Pembaruan data permohonan melalui API.',
                );
            });

            return $this->success(
                new ProjectResource($project->fresh()->load(['documentType', 'applicant', 'documents'])),
                'Permohonan dokumen berhasil diperbarui. Jika ada berkas baru, upload diproses melalui antrean.',
            );
        } catch (\Throwable $e) {
            Log::error('Failed to update API project.', ['project_id' => $id, 'error' => $e->getMessage()]);

            return $this->error('Gagal memperbarui permohonan.', 500);
        }
    }
}
