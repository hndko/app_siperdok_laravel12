<?php

use App\Http\Controllers\Api\Modules\Assessments\HistoryApiController;
use App\Http\Controllers\Api\Modules\Assessments\StartReviewApiController;
use App\Http\Controllers\Api\Modules\Assessments\StoreAssessmentApiController;
use App\Http\Controllers\Api\Auth\LoginApiController;
use App\Http\Controllers\Api\Auth\LogoutApiController;
use App\Http\Controllers\Api\Auth\RegisterApiController;
use App\Http\Controllers\Api\Modules\DocumentTypes\IndexDocumentTypeApiController;
use App\Http\Controllers\Api\Modules\Projects\IndexProjectApiController;
use App\Http\Controllers\Api\Modules\Projects\ShowProjectApiController;
use App\Http\Controllers\Api\Modules\Projects\StoreProjectApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public auth
    Route::post('/login', LoginApiController::class);
    Route::post('/register', RegisterApiController::class);

    // Sanctum Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', LogoutApiController::class);

        Route::get('/projects', IndexProjectApiController::class);
        Route::post('/projects', StoreProjectApiController::class);
        Route::get('/projects/{id}', ShowProjectApiController::class);

        Route::post('/assessments/{id}/start-review', StartReviewApiController::class);
        Route::post('/assessments/{id}', StoreAssessmentApiController::class);
        Route::get('/assessments/history', HistoryApiController::class);

        Route::get('/document-types', IndexDocumentTypeApiController::class);
    });
});
