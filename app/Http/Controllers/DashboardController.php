<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // 1. Common KPI Stats
        if ($user->hasRole('admin') || $user->hasRole('penilai')) {
            $totalProjects = Project::count();
            $approvedCount = Project::where('status', 'approved')->count();
            $revisionCount = Project::where('status', 'revision')->count();
            $rejectedCount = Project::where('status', 'rejected')->count();
            $pendingCount = Project::whereIn('status', ['submitted', 'in_review'])->count();
            $draftCount = Project::where('status', 'draft')->count();

            // Recent project activities
            $recentProjects = Project::with(['applicant', 'documentType'])
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();
        } else {
            // Pemohon KPI Stats
            $totalProjects = Project::where('applicant_id', $user->id)->count();
            $approvedCount = Project::where('applicant_id', $user->id)->where('status', 'approved')->count();
            $revisionCount = Project::where('applicant_id', $user->id)->where('status', 'revision')->count();
            $rejectedCount = Project::where('applicant_id', $user->id)->where('status', 'rejected')->count();
            $pendingCount = Project::where('applicant_id', $user->id)->whereIn('status', ['submitted', 'in_review'])->count();
            $draftCount = Project::where('applicant_id', $user->id)->where('status', 'draft')->count();

            $recentProjects = Project::with(['documentType'])
                ->where('applicant_id', $user->id)
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();
        }

        // 2. Chart.js Data (Cross-Database Compatible: PostgreSQL, MySQL, SQLite)
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            $monthExpr = "to_char(created_at, 'YYYY-MM')";
        } elseif ($driver === 'mysql') {
            $monthExpr = "DATE_FORMAT(created_at, '%Y-%m')";
        } else {
            $monthExpr = "strftime('%Y-%m', created_at)";
        }

        $monthlyChartData = Project::select(
                DB::raw("{$monthExpr} as month"),
                DB::raw("count(*) as count")
            )
            ->when(!$user->hasRole('admin') && !$user->hasRole('penilai'), function ($q) use ($user) {
                $q->where('applicant_id', $user->id);
            })
            ->groupBy(DB::raw($monthExpr))
            ->orderBy(DB::raw($monthExpr), 'asc')
            ->limit(12)
            ->get();

        $chartLabels = $monthlyChartData->pluck('month')->map(function ($m) {
            return date('M Y', strtotime($m . '-01'));
        });
        $chartValues = $monthlyChartData->pluck('count');

        // Status doughnut chart data
        $statusCounts = [
            'Draft' => $draftCount,
            'Diproses / Dalam Penilaian' => $pendingCount,
            'Perlu Revisi' => $revisionCount,
            'Disetujui' => $approvedCount,
            'Ditolak' => $rejectedCount,
        ];

        return view('dashboard.index', compact(
            'totalProjects',
            'approvedCount',
            'revisionCount',
            'rejectedCount',
            'pendingCount',
            'draftCount',
            'recentProjects',
            'chartLabels',
            'chartValues',
            'statusCounts'
        ));
    }
}
