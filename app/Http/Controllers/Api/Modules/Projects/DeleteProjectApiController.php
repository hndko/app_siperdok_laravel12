<?php

namespace App\Http\Controllers\Api\Modules\Projects;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class DeleteProjectApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request, int $id)
    {
        $project = Project::findOrFail($id);

        $this->authorize('delete', $project);
        $project->delete();

        return $this->success(message: 'Draft permohonan berhasil dihapus.');
    }
}
