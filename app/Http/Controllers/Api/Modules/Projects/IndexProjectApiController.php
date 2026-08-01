<?php

namespace App\Http\Controllers\Api\Modules\Projects;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class IndexProjectApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:draft,submitted,in_review,revision,approved,rejected,certificate_issued'],
            'search' => ['nullable', 'string', 'max:100'],
            'document_type_id' => ['nullable', 'integer', 'exists:document_types,id'],
            'applicant_id' => ['nullable', 'integer', 'exists:users,id'],
            'evaluator_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
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
}
