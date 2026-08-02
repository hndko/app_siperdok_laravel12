<?php

use App\Http\Controllers\Api\Auth\LoginApiController;
use App\Http\Controllers\Api\Auth\LogoutApiController;
use App\Http\Controllers\Api\Auth\MeApiController;
use App\Http\Controllers\Api\Auth\RegisterApiController;
use App\Http\Controllers\Api\Auth\UpdateProfileApiController;
use App\Http\Controllers\Api\Certificates\VerifyCertificateApiController;
use App\Http\Controllers\Api\Modules\Assessments\HistoryApiController;
use App\Http\Controllers\Api\Modules\Assessments\StartReviewApiController;
use App\Http\Controllers\Api\Modules\Assessments\StoreAssessmentApiController;
use App\Http\Controllers\Api\Modules\Dashboard\ShowDashboardApiController;
use App\Http\Controllers\Api\Modules\DocumentTypes\IndexDocumentTypeApiController;
use App\Http\Controllers\Api\Modules\Exports\ExportCertificatePdfApiController;
use App\Http\Controllers\Api\Modules\Exports\ExportProjectsCsvApiController;
use App\Http\Controllers\Api\Modules\Exports\ExportProjectsXlsxApiController;
use App\Http\Controllers\Api\Modules\Projects\DeleteProjectApiController;
use App\Http\Controllers\Api\Modules\Projects\IndexProjectApiController;
use App\Http\Controllers\Api\Modules\Projects\IssueCertificateApiController;
use App\Http\Controllers\Api\Modules\Projects\ShowProjectApiController;
use App\Http\Controllers\Api\Modules\Projects\StoreProjectApiController;
use App\Http\Controllers\Api\Modules\Projects\UpdateProjectApiController;
use App\Http\Controllers\Api\Modules\Projects\VerificationChecklists\BulkUpdateVerificationChecklistApiController;
use App\Http\Controllers\Api\Modules\Projects\VerificationChecklists\IndexVerificationChecklistApiController;
use App\Http\Controllers\Api\Modules\Users\IndexUserApiController;
use App\Http\Controllers\Api\Modules\Users\UpdateUserApiController;
use App\Http\Controllers\Api\Notifications\IndexNotificationApiController;
use App\Http\Controllers\Api\Notifications\MarkAllNotificationsReadApiController;
use App\Http\Controllers\Api\Notifications\MarkNotificationReadApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public auth
    Route::post('/login', LoginApiController::class);
    Route::post('/register', RegisterApiController::class);
    Route::get('/certificates/verify/{certificateNumber}', VerifyCertificateApiController::class)
        ->middleware('throttle:20,1')
        ->where('certificateNumber', '.*');

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
        Route::get('/projects/{id}/verification-checklists', IndexVerificationChecklistApiController::class);
        Route::put('/projects/{id}/verification-checklists', BulkUpdateVerificationChecklistApiController::class);
        Route::post('/projects/{id}/issue-certificate', IssueCertificateApiController::class);

        Route::post('/assessments/{id}/start-review', StartReviewApiController::class);
        Route::post('/assessments/{id}', StoreAssessmentApiController::class);
        Route::get('/assessments/history', HistoryApiController::class);

        Route::get('/users', IndexUserApiController::class);
        Route::match(['put', 'patch'], '/users/{id}', UpdateUserApiController::class);
        Route::get('/document-types', IndexDocumentTypeApiController::class);

        Route::get('/notifications', IndexNotificationApiController::class);
        Route::patch('/notifications/read-all', MarkAllNotificationsReadApiController::class);
        Route::patch('/notifications/{notification}/read', MarkNotificationReadApiController::class);

        Route::get('/exports/projects/csv', ExportProjectsCsvApiController::class);
        Route::get('/exports/projects/xlsx', ExportProjectsXlsxApiController::class);
        Route::get('/exports/projects/{id}/certificate', ExportCertificatePdfApiController::class);
    });
});
