<?php

namespace App\Http\Controllers\Api\Modules\Projects;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ShowProjectApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request, int $id)
    {
        $project = Project::with(['documentType', 'applicant', 'evaluator', 'documents', 'assessmentLogs.user'])
            ->findOrFail($id);

        $this->authorize('view', $project);

        return $this->success(new ProjectResource($project));
    }
}
