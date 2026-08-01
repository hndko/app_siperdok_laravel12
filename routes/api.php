<?php

use App\Http\Controllers\Api\Modules\Assessments\HistoryApiController;
use App\Http\Controllers\Api\Modules\Assessments\StartReviewApiController;
use App\Http\Controllers\Api\Modules\Assessments\StoreAssessmentApiController;
use App\Http\Controllers\Api\Auth\LoginApiController;
use App\Http\Controllers\Api\Auth\LogoutApiController;
use App\Http\Controllers\Api\Auth\MeApiController;
use App\Http\Controllers\Api\Auth\RegisterApiController;
use App\Http\Controllers\Api\Auth\UpdateProfileApiController;
use App\Http\Controllers\Api\Modules\DocumentTypes\IndexDocumentTypeApiController;
use App\Http\Controllers\Api\Modules\Dashboard\ShowDashboardApiController;
use App\Http\Controllers\Api\Modules\Exports\ExportCertificatePdfApiController;
use App\Http\Controllers\Api\Modules\Exports\ExportProjectsCsvApiController;
use App\Http\Controllers\Api\Modules\Exports\ExportProjectsXlsxApiController;
use App\Http\Controllers\Api\Modules\Projects\DeleteProjectApiController;
use App\Http\Controllers\Api\Modules\Projects\IndexProjectApiController;
use App\Http\Controllers\Api\Modules\Projects\ShowProjectApiController;
use App\Http\Controllers\Api\Modules\Projects\StoreProjectApiController;
use App\Http\Controllers\Api\Modules\Projects\UpdateProjectApiController;
use App\Http\Controllers\Api\Modules\Users\IndexUserApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public auth
    Route::post('/login', LoginApiController::class);
    Route::post('/register', RegisterApiController::class);

    // Sanctum Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', MeApiController::class);
        Route::put('/profile', UpdateProfileApiController::class);
        Route::post('/profile', UpdateProfileApiController::class);
        Route::post('/logout', LogoutApiController::class);

        Route::get('/dashboard', ShowDashboardApiController::class);

        Route::get('/projects', IndexProjectApiController::class);
        Route::post('/projects', StoreProjectApiController::class);
        Route::get('/projects/{id}', ShowProjectApiController::class);
        Route::match(['put', 'post'], '/projects/{id}', UpdateProjectApiController::class);
        Route::delete('/projects/{id}', DeleteProjectApiController::class);

        Route::post('/assessments/{id}/start-review', StartReviewApiController::class);
        Route::post('/assessments/{id}', StoreAssessmentApiController::class);
        Route::get('/assessments/history', HistoryApiController::class);

        Route::get('/users', IndexUserApiController::class);
        Route::get('/document-types', IndexDocumentTypeApiController::class);

        Route::get('/exports/projects/csv', ExportProjectsCsvApiController::class);
        Route::get('/exports/projects/xlsx', ExportProjectsXlsxApiController::class);
        Route::get('/exports/projects/{id}/certificate', ExportCertificatePdfApiController::class);
    });
});
