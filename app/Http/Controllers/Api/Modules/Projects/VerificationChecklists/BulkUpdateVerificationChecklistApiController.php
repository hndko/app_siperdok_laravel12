<?php

namespace App\Http\Controllers\Api\Modules\Projects\VerificationChecklists;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\BulkUpdateVerificationChecklistRequest;
use App\Http\Resources\VerificationChecklistResource;
use App\Models\AssessmentLog;
use App\Models\Project;
use App\Models\ProjectVerificationChecklist;
use App\Models\VerificationChecklistItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BulkUpdateVerificationChecklistApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(BulkUpdateVerificationChecklistRequest $request, int $project)
    {
        $actor = $request->user();
        $validated = $request->validated();

        abort_unless($actor->hasRole('admin') || $actor->hasRole('penilai'), 403);

        $checklists = DB::transaction(function () use ($validated, $project, $actor) {
            $projectModel = Project::query()->lockForUpdate()->findOrFail($project);
            abort_unless($projectModel->status === Project::STATUS_IN_REVIEW, 422, 'Checklist hanya dapat diisi saat permohonan berstatus in_review.');

            $itemIds = collect($validated['items'])->pluck('checklist_item_id')->unique()->values();
            $activeItems = VerificationChecklistItem::query()
                ->whereIn('id', $itemIds)
                ->where('is_active', true)
                ->pluck('id')
                ->all();

            if (count($activeItems) !== $itemIds->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Semua checklist item harus aktif.',
                ]);
            }

            foreach ($validated['items'] as $item) {
                ProjectVerificationChecklist::updateOrCreate(
                    [
                        'project_id' => $projectModel->id,
                        'checklist_item_id' => $item['checklist_item_id'],
                    ],
                    [
                        'reviewer_id' => $actor->id,
                        'status' => $item['status'],
                        'notes' => $item['notes'] ?? null,
                        'checked_at' => now(),
                    ],
                );
            }

            $projectModel->verificationChecklists()->with('item')->get();
            $requiredPending = $projectModel->verificationChecklists()
                ->whereHas('item', fn ($query) => $query->where('is_required', true))
                ->where('status', ProjectVerificationChecklist::STATUS_PENDING)
                ->count();

            AssessmentLog::create([
                'project_id' => $projectModel->id,
                'user_id' => $actor->id,
                'action' => $requiredPending === 0 ? 'verification_checklist_completed' : 'verification_checklist_updated',
                'previous_status' => $projectModel->status,
                'new_status' => $projectModel->status,
                'notes' => 'Checklist verifikasi administrasi diperbarui.',
            ]);

            return ProjectVerificationChecklist::query()
                ->with(['item', 'reviewer'])
                ->where('project_id', $projectModel->id)
                ->orderBy(
                    VerificationChecklistItem::select('sort_order')
                        ->whereColumn('verification_checklist_items.id', 'project_verification_checklists.checklist_item_id')
                        ->limit(1)
                )
                ->get();
        });

        return $this->success([
            'items' => VerificationChecklistResource::collection($checklists),
        ], 'Checklist verifikasi berhasil disimpan.');
    }
}
