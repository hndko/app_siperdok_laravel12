<?php

namespace App\Http\Controllers\Api\Modules\Assessments;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssessmentLogResource;
use App\Models\AssessmentLog;
use App\Models\User;
use Illuminate\Http\Request;

class HistoryApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'action' => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
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
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->whereHas('project', function ($projectQuery) use ($search) {
                    $projectQuery
                        ->where('project_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->cursorPaginate($validated['per_page'] ?? 20)
            ->withQueryString();

        return $this->success(AssessmentLogResource::collection($logs));
    }
}
