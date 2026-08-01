<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssessmentLogResource;
use App\Http\Resources\DocumentTypeResource;
use App\Http\Resources\ProjectResource;
use App\Models\AssessmentLog;
use App\Models\DocumentType;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use App\Services\ProjectWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProjectApiController extends Controller
{
    public function __construct(private readonly ProjectWorkflowService $workflow) {}

    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'status' => 'nullable|string|in:draft,submitted,in_review,revision,approved,rejected',
            'search' => 'nullable|string|max:100',
            'document_type_id' => 'nullable|integer|exists:document_types,id',
            'applicant_id' => 'nullable|integer|exists:users,id',
            'evaluator_id' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $projects = Project::query()
            ->select(['id', 'project_number', 'title', 'applicant_id', 'evaluator_id', 'document_type_id', 'status', 'description', 'submitted_at', 'approved_at', 'rejected_at', 'created_at', 'updated_at'])
            ->with([
                'documentType:id,code,name,description,required_files,is_active',
                'applicant:id,name,email,phone,nip_nik,company_name',
                'evaluator:id,name,email,phone,nip_nik,company_name',
            ])
            ->visibleTo($user)
            ->filter($validated)
            ->orderByDesc('created_at')
            ->cursorPaginate($validated['per_page'] ?? 15)
            ->withQueryString();

        return $this->success(ProjectResource::collection($projects));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'document_type_id' => 'required|exists:document_types,id',
            'description' => 'nullable|string',
            'document' => 'required|file|mimes:pdf,docx,doc,png,jpg,jpeg|max:10240',
            'submit_action' => 'required|in:draft,submit',
        ]);

        try {
            $project = DB::transaction(function () use ($validated, $request, $user) {
                $projectNum = 'PRJ-' . date('Ym') . '-' . str_pad((Project::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
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
                $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
                $path = $file->storeAs('project_documents/' . $project->id, $filename, 'public');

                ProjectDocument::create([
                    'project_id' => $project->id,
                    'document_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_name' => $filename,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getClientMimeType(),
                    'version' => 1,
                    'uploaded_by' => $user->id,
                ]);

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
                message: 'Permohonan dokumen berhasil disimpan.',
                code: 201,
            );
        } catch (\Exception $e) {
            Log::error('Failed to store API project.', ['error' => $e->getMessage()]);
            return $this->error('Gagal menyimpan permohonan.', 500);
        }
    }

    public function show(Request $request, $id)
    {
        $project = Project::with(['documentType', 'applicant', 'evaluator', 'documents', 'assessmentLogs.user'])
            ->findOrFail($id);

        $this->authorize('view', $project);

        return $this->success(new ProjectResource($project));
    }

    public function startReview(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $project = $this->workflow->startReview((int) $id, $request->user(), $validated['notes'] ?? null);

            return $this->success(
                data: new ProjectResource($project->load(['documentType', 'applicant', 'evaluator', 'assessmentLogs.user'])),
                message: 'Review permohonan berhasil dimulai.',
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Failed to start project review via API.', ['project_id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal memulai review permohonan.', 500);
        }
    }

    public function assess(Request $request, $id)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'decision' => 'required|in:approved,revision,rejected',
            'notes' => 'required|string|min:5',
        ]);

        try {
            $project = $this->workflow->decide((int) $id, $user, $validated['decision'], $validated['notes']);

            return $this->success(
                data: new ProjectResource($project->load(['documentType', 'applicant', 'evaluator', 'assessmentLogs.user'])),
                message: 'Penilaian permohonan berhasil diperbarui.',
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to assess API project.', ['project_id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal memproses penilaian permohonan.', 500);
        }
    }

    public function documentTypes()
    {
        $documentTypes = DocumentType::where('is_active', true)->get();
        return $this->success(DocumentTypeResource::collection($documentTypes));
    }

    public function history(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'project_id' => 'nullable|integer|exists:projects,id',
            'action' => 'nullable|string|max:50',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $logs = AssessmentLog::query()
            ->with([
                'project.documentType:id,code,name',
                'project.applicant:id,name,email,phone,nip_nik,company_name',
                'project.evaluator:id,name,email,phone,nip_nik,company_name',
                'user:id,name,email,phone,nip_nik,company_name',
            ])
            ->whereHas('project', fn ($query) => $query->visibleTo($user))
            ->when($validated['project_id'] ?? null, fn ($query, $id) => $query->where('project_id', $id))
            ->when($validated['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->orderByDesc('created_at')
            ->cursorPaginate($validated['per_page'] ?? 20)
            ->withQueryString();

        return $this->success(AssessmentLogResource::collection($logs));
    }

    private function success(mixed $data = null, string $message = 'OK', int $code = 200)
    {
        if ($data instanceof \Illuminate\Http\Resources\Json\JsonResource || $data instanceof \Illuminate\Http\Resources\Json\ResourceCollection) {
            return $data->additional([
                'status' => 'success',
                'message' => $message,
            ])->response()->setStatusCode($code);
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    private function error(string $message, int $code)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }
}
