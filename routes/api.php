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
        Route::get('/projects/{id}', [ProjectApiController::class, 'show']);
        Route::post('/assessments/{id}', [ProjectApiController::class, 'assess']);
    });
});
