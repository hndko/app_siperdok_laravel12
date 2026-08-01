<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ProjectApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public auth
    Route::post('/login', [AuthApiController::class, 'login']);

    // Sanctum Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthApiController::class, 'logout']);

        Route::get('/projects', [ProjectApiController::class, 'index']);
        Route::post('/projects', [ProjectApiController::class, 'store']);
        Route::get('/projects/{id}', [ProjectApiController::class, 'show']);

        Route::post('/assessments/{id}', [ProjectApiController::class, 'assess']);
        Route::get('/assessments/history', [ProjectApiController::class, 'history']);

        Route::get('/document-types', [ProjectApiController::class, 'documentTypes']);
    });
});
