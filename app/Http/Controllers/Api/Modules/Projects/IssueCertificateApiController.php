<?php

namespace App\Http\Controllers\Api\Modules\Projects;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class IssueCertificateApiController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly ProjectWorkflowService $workflow) {}

    public function __invoke(Request $request, int $id)
    {
        try {
            $project = $this->workflow->issueCertificate($id, $request->user());

            return $this->success(
                new ProjectResource($project->load(['documentType', 'applicant', 'evaluator', 'certificateIssuer'])),
                'Certificate berhasil diterbitkan.',
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Failed to issue certificate.', ['project_id' => $id, 'error' => $e->getMessage()]);

            return $this->error('Gagal menerbitkan certificate.', 500);
        }
    }
}
