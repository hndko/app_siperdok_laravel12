<?php

namespace App\Services;

use App\Http\Resources\ProjectListResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function dataFor(User $user): array
    {
        $stats = Cache::remember($this->cacheKey($user), now()->addSeconds(60), function () use ($user) {
            $baseQuery = Project::query()->visibleTo($user);
            $counts = $this->statusCounts((clone $baseQuery));
            $monthlyChartData = $this->monthlyChartData((clone $baseQuery));

            return [
                'total_projects' => $counts['total'],
                'approved_count' => $counts[Project::STATUS_APPROVED],
                'certificate_issued_count' => $counts[Project::STATUS_CERTIFICATE_ISSUED],
                'revision_count' => $counts[Project::STATUS_REVISION],
                'rejected_count' => $counts[Project::STATUS_REJECTED],
                'submitted_count' => $counts[Project::STATUS_SUBMITTED],
                'in_review_count' => $counts[Project::STATUS_IN_REVIEW],
                'pending_count' => $counts[Project::STATUS_SUBMITTED] + $counts[Project::STATUS_IN_REVIEW],
                'draft_count' => $counts[Project::STATUS_DRAFT],
                'incomplete_checklist_count' => $this->incompleteChecklistCount((clone $baseQuery)),
                'ready_to_issue_count' => $counts[Project::STATUS_APPROVED],
                'chart_labels' => $monthlyChartData->pluck('month')->map(fn ($month) => date('M Y', strtotime($month.'-01'))),
                'chart_values' => $monthlyChartData->pluck('count'),
                'status_counts' => [
                    'Draft' => $counts[Project::STATUS_DRAFT],
                    'Diproses / Dalam Penilaian' => $counts[Project::STATUS_SUBMITTED] + $counts[Project::STATUS_IN_REVIEW],
                    'Perlu Revisi' => $counts[Project::STATUS_REVISION],
                    'Disetujui' => $counts[Project::STATUS_APPROVED],
                    'Certificate Terbit' => $counts[Project::STATUS_CERTIFICATE_ISSUED],
                    'Ditolak' => $counts[Project::STATUS_REJECTED],
                ],
            ];
        });

        $recentProjects = Project::query()
            ->select([
                'id',
                'project_number',
                'title',
                'applicant_id',
                'evaluator_id',
                'document_type_id',
                'status',
                'description',
                'submitted_at',
                'approved_at',
                'rejected_at',
                'certificate_number',
                'certificate_issued_at',
                'created_at',
                'updated_at',
            ])
            ->with([
                'applicant:id,name,email,phone,nip_nik,company_name',
                'evaluator:id,name,email,phone,nip_nik,company_name',
                'documentType:id,code,name,description,required_files,is_active',
            ])
            ->visibleTo($user)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return [
            ...$stats,
            'recent_projects' => ProjectListResource::collection($recentProjects),
        ];
    }

    private function cacheKey(User $user): string
    {
        $roleKey = $user->getRoleNames()->sort()->implode('|') ?: 'guest';

        return "dashboard:v2:user:{$user->id}:roles:{$roleKey}";
    }

    private function statusCounts($query): array
    {
        $row = $query
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as draft_count', [Project::STATUS_DRAFT])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as submitted_count', [Project::STATUS_SUBMITTED])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as in_review_count', [Project::STATUS_IN_REVIEW])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as revision_count', [Project::STATUS_REVISION])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved_count', [Project::STATUS_APPROVED])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected_count', [Project::STATUS_REJECTED])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as certificate_issued_count', [Project::STATUS_CERTIFICATE_ISSUED])
            ->first();

        return [
            'total' => (int) $row->total,
            Project::STATUS_DRAFT => (int) $row->draft_count,
            Project::STATUS_SUBMITTED => (int) $row->submitted_count,
            Project::STATUS_IN_REVIEW => (int) $row->in_review_count,
            Project::STATUS_REVISION => (int) $row->revision_count,
            Project::STATUS_APPROVED => (int) $row->approved_count,
            Project::STATUS_REJECTED => (int) $row->rejected_count,
            Project::STATUS_CERTIFICATE_ISSUED => (int) $row->certificate_issued_count,
        ];
    }

    private function incompleteChecklistCount($query): int
    {
        return (int) $query
            ->where('status', Project::STATUS_IN_REVIEW)
            ->whereExists(function ($subquery) {
                $subquery->selectRaw('1')
                    ->from('project_verification_checklists')
                    ->whereColumn('project_verification_checklists.project_id', 'projects.id')
                    ->where('project_verification_checklists.status', 'pending');
            })
            ->count();
    }

    private function monthlyChartData($query): Collection
    {
        $driver = DB::getDriverName();
        $monthExpr = match ($driver) {
            'pgsql' => "to_char(created_at, 'YYYY-MM')",
            'mysql' => "DATE_FORMAT(created_at, '%Y-%m')",
            default => "strftime('%Y-%m', created_at)",
        };

        return $query
            ->select(DB::raw("{$monthExpr} as month"), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy(DB::raw($monthExpr))
            ->orderBy(DB::raw($monthExpr))
            ->get();
    }
}
