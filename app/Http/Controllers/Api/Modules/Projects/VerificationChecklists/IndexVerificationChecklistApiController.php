<?php

namespace App\Http\Controllers\Api\Modules\Projects\VerificationChecklists;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\VerificationChecklistResource;
use App\Models\Project;
use App\Models\ProjectVerificationChecklist;
use App\Models\VerificationChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IndexVerificationChecklistApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request, int $project)
    {
        $projectModel = Project::query()
            ->select(['id', 'applicant_id', 'evaluator_id', 'status'])
            ->findOrFail($project);
        $this->authorize('view', $projectModel);

        $items = Cache::remember('verification-checklist-items:active:v1', now()->addMinutes(10), function () {
            return VerificationChecklistItem::query()
                ->select(['id', 'name', 'description', 'is_required', 'is_active', 'sort_order'])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        });

        $existing = ProjectVerificationChecklist::query()
            ->select(['id', 'project_id', 'checklist_item_id', 'reviewer_id', 'status', 'notes', 'checked_at'])
            ->with([
                'item:id,name,description,is_required,is_active,sort_order',
                'reviewer:id,name,email,phone,nip_nik,company_name',
            ])
            ->where('project_id', $projectModel->id)
            ->get()
            ->keyBy('checklist_item_id');

        $checklists = $items->map(function (VerificationChecklistItem $item) use ($existing, $projectModel) {
            return $existing->get($item->id) ?? (new ProjectVerificationChecklist([
                'project_id' => $projectModel->id,
                'checklist_item_id' => $item->id,
                'status' => ProjectVerificationChecklist::STATUS_PENDING,
            ]))->setRelation('item', $item);
        });

        $summary = $this->summary($checklists);

        return $this->success([
            'project_status' => $projectModel->status,
            'can_update' => ($request->user()->hasRole('admin') || $request->user()->hasRole('penilai'))
                && $projectModel->status === Project::STATUS_IN_REVIEW,
            'items' => VerificationChecklistResource::collection($checklists),
            'summary' => $summary,
        ]);
    }

    private function summary($checklists): array
    {
        $required = $checklists->filter(fn ($checklist) => (bool) $checklist->item?->is_required);

        return [
            'total' => $checklists->count(),
            'required_total' => $required->count(),
            'passed' => $checklists->where('status', ProjectVerificationChecklist::STATUS_PASSED)->count(),
            'failed' => $checklists->where('status', ProjectVerificationChecklist::STATUS_FAILED)->count(),
            'pending' => $checklists->where('status', ProjectVerificationChecklist::STATUS_PENDING)->count(),
            'not_applicable' => $checklists->where('status', ProjectVerificationChecklist::STATUS_NOT_APPLICABLE)->count(),
            'required_pending' => $required->where('status', ProjectVerificationChecklist::STATUS_PENDING)->count(),
            'required_failed' => $required->where('status', ProjectVerificationChecklist::STATUS_FAILED)->count(),
            'progress_percent' => $checklists->count() ? round(($checklists->where('status', '!=', ProjectVerificationChecklist::STATUS_PENDING)->count() / $checklists->count()) * 100) : 0,
        ];
    }
}
