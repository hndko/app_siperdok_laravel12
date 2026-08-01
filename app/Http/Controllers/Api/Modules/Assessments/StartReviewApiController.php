<?php

namespace App\Http\Controllers\Api\Modules\Assessments;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StartReviewApiController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly ProjectWorkflowService $workflow) {}

    public function __invoke(Request $request, int $id)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $project = $this->workflow->startReview($id, $request->user(), $validated['notes'] ?? null);

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
}
