<?php

namespace App\Http\Controllers\Api\Modules\Assessments;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\User;
use App\Services\ProjectWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StoreAssessmentApiController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly ProjectWorkflowService $workflow) {}

    public function __invoke(Request $request, int $id)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'decision' => ['required', 'in:approved,revision,rejected'],
            'notes' => ['required', 'string', 'min:5'],
        ]);

        try {
            $project = $this->workflow->decide($id, $user, $validated['decision'], $validated['notes']);

            return $this->success(
                data: new ProjectResource($project->load(['documentType', 'applicant', 'evaluator', 'assessmentLogs.user'])),
                message: 'Penilaian permohonan berhasil diperbarui.',
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Failed to assess API project.', ['project_id' => $id, 'error' => $e->getMessage()]);

            return $this->error('Gagal memproses penilaian permohonan.', 500);
        }
    }
}
