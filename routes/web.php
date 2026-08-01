<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => redirect('/dashboard'));

Route::get('/{path}', function (string $path = '') {
    return Inertia::render(resolveSpaPage($path), resolveSpaProps($path));
})->where('path', '^(?!api/|storage/|up$).*$');

if (!function_exists('resolveSpaPage')) {
    function resolveSpaPage(string $path): string
    {
        $path = trim($path, '/');

        return match (true) {
            $path === 'login' => 'Auth/Login',
            $path === 'register' => 'Auth/Register',
            $path === 'dashboard' => 'Dashboard',
            $path === 'projects' => 'Projects/Index',
            $path === 'projects/create' => 'Projects/Create',
            preg_match('#^projects/\d+/edit$#', $path) === 1 => 'Projects/Edit',
            preg_match('#^projects/\d+$#', $path) === 1 => 'Projects/Show',
            $path === 'assessments' => 'Assessments/Index',
            $path === 'assessments/history' => 'Assessments/History',
            preg_match('#^assessments/\d+/review$#', $path) === 1 => 'Assessments/Review',
            $path === 'master/users' => 'Master/Users',
            $path === 'master/document-types' => 'Master/DocumentTypes',
            preg_match('#^exports/projects/\d+/certificate/preview$#', $path) === 1 => 'Exports/CertificatePreview',
            default => 'Dashboard',
        };
    }
}

if (!function_exists('resolveSpaProps')) {
    function resolveSpaProps(string $path): array
    {
        $path = trim($path, '/');
        $emptyPaginator = ['data' => [], 'next_cursor' => null, 'prev_cursor' => null, 'path' => url()->current()];
        $emptyProject = [
            'id' => null,
            'project_number' => '-',
            'title' => '-',
            'status' => 'draft',
            'description' => null,
            'document_type' => null,
            'applicant' => null,
            'evaluator' => null,
            'documents' => [],
            'assessment_logs' => [],
        ];

        return match (true) {
            $path === 'dashboard' => [
                'totalProjects' => 0,
                'approvedCount' => 0,
                'revisionCount' => 0,
                'rejectedCount' => 0,
                'pendingCount' => 0,
                'draftCount' => 0,
                'recentProjects' => [],
                'chartLabels' => [],
                'chartValues' => [],
                'statusCounts' => [],
            ],
            $path === 'projects' || $path === 'assessments' => [
                'projects' => $emptyPaginator,
                'documentTypes' => [],
                'filters' => request()->only(['search', 'status', 'document_type_id']),
            ],
            $path === 'projects/create' => ['documentTypes' => []],
            preg_match('#^projects/\d+/edit$#', $path) === 1 => ['project' => $emptyProject, 'documentTypes' => []],
            preg_match('#^projects/\d+$#', $path) === 1 => ['project' => $emptyProject],
            $path === 'assessments/history' => ['logs' => $emptyPaginator, 'filters' => request()->only(['search', 'action'])],
            preg_match('#^assessments/\d+/review$#', $path) === 1 => [
                'project' => $emptyProject,
                'canStartReview' => false,
                'canAssess' => false,
            ],
            $path === 'master/users' => ['users' => $emptyPaginator, 'filters' => request()->only(['search', 'role'])],
            $path === 'master/document-types' => ['documentTypes' => []],
            preg_match('#^exports/projects/\d+/certificate/preview$#', $path) === 1 => ['project' => $emptyProject],
            default => [],
        };
    }
}
