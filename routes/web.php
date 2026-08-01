<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Master\DocumentTypeController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Pemohon\ProjectController;
use App\Http\Controllers\Penilai\AssessmentController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Redirect root to dashboard/login
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pemohon & Common Project Routes
    Route::resource('projects', ProjectController::class);

    // Penilai & Evaluator Assessment Routes
    Route::middleware('role:penilai|admin')->group(function () {
        Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');
        Route::get('/assessments/{id}/review', [AssessmentController::class, 'review'])->name('assessments.review');
        Route::post('/assessments/{id}/process', [AssessmentController::class, 'processDecision'])->name('assessments.process');
        Route::get('/assessments/history', [AssessmentController::class, 'history'])->name('assessments.history');
    });

    // Admin Master Data Routes
    Route::middleware('role:admin')->prefix('master')->name('master.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('document-types', DocumentTypeController::class)->except(['create', 'show', 'destroy']);
    });

    // Export Routes
    Route::get('/export/projects/csv', [ExportController::class, 'exportProjectsCsv'])->name('export.projects.csv');
    Route::get('/export/projects/{id}/certificate/preview', [ExportController::class, 'previewCertificate'])->name('export.certificate.preview');
    Route::get('/export/projects/{id}/certificate', [ExportController::class, 'exportCertificatePdf'])->name('export.certificate.pdf');
});
