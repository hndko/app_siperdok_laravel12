<?php

namespace App\Http\Controllers\Api\Modules\Dashboard;

use App\Http\Controllers\Api\Modules\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShowDashboardApiController extends Controller
{
    use RespondsWithApi;

    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $query = Project::query()->visibleTo($user);

        $totalProjects = (clone $query)->count();
        $approvedCount = (clone $query)->where('status', Project::STATUS_APPROVED)->count();
        $certificateIssuedCount = (clone $query)->where('status', Project::STATUS_CERTIFICATE_ISSUED)->count();
        $revisionCount = (clone $query)->where('status', Project::STATUS_REVISION)->count();
        $rejectedCount = (clone $query)->where('status', Project::STATUS_REJECTED)->count();
        $submittedCount = (clone $query)->where('status', Project::STATUS_SUBMITTED)->count();
        $inReviewCount = (clone $query)->where('status', Project::STATUS_IN_REVIEW)->count();
        $pendingCount = (clone $query)->whereIn('status', [Project::STATUS_SUBMITTED, Project::STATUS_IN_REVIEW])->count();
        $draftCount = (clone $query)->where('status', Project::STATUS_DRAFT)->count();
        $incompleteChecklistCount = (clone $query)
            ->where('status', Project::STATUS_IN_REVIEW)
            ->whereHas('verificationChecklists', fn ($checklist) => $checklist->where('status', 'pending'))
            ->count();
        $readyToIssueCount = (clone $query)->where('status', Project::STATUS_APPROVED)->count();

        $recentProjects = Project::query()
            ->with(['applicant', 'evaluator', 'documentType'])
            ->visibleTo($user)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        $driver = DB::getDriverName();
        $monthExpr = match ($driver) {
            'pgsql' => "to_char(created_at, 'YYYY-MM')",
            'mysql' => "DATE_FORMAT(created_at, '%Y-%m')",
            default => "strftime('%Y-%m', created_at)",
        };

        $monthlyChartData = Project::query()
            ->select(DB::raw("{$monthExpr} as month"), DB::raw('count(*) as count'))
            ->visibleTo($user)
            ->groupBy(DB::raw($monthExpr))
            ->orderBy(DB::raw($monthExpr))
            ->limit(12)
            ->get();

        return $this->success([
            'total_projects' => $totalProjects,
            'approved_count' => $approvedCount,
            'certificate_issued_count' => $certificateIssuedCount,
            'revision_count' => $revisionCount,
            'rejected_count' => $rejectedCount,
            'submitted_count' => $submittedCount,
            'in_review_count' => $inReviewCount,
            'pending_count' => $pendingCount,
            'draft_count' => $draftCount,
            'incomplete_checklist_count' => $incompleteChecklistCount,
            'ready_to_issue_count' => $readyToIssueCount,
            'recent_projects' => ProjectResource::collection($recentProjects),
            'chart_labels' => $monthlyChartData->pluck('month')->map(fn ($month) => date('M Y', strtotime($month.'-01'))),
            'chart_values' => $monthlyChartData->pluck('count'),
            'status_counts' => [
                'Draft' => $draftCount,
                'Diproses / Dalam Penilaian' => $pendingCount,
                'Perlu Revisi' => $revisionCount,
                'Disetujui' => $approvedCount,
                'Certificate Terbit' => $certificateIssuedCount,
                'Ditolak' => $rejectedCount,
            ],
        ]);
    }
}
