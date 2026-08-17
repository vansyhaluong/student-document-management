<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\PublicStudentDocumentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentDocumentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicStudentDocumentController::class, 'index'])->name('home');
Route::post('/tra-cuu-ho-so', [PublicStudentDocumentController::class, 'lookup'])
    ->middleware('throttle:30,1')
    ->name('public.documents.lookup');
Route::post('/nop-ho-so', [PublicStudentDocumentController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('public.documents.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::patch('/documents/{document}/assignment', [StudentDocumentController::class, 'assign'])
        ->name('documents.assignment');
    Route::post('/documents/{document}/accept', [StudentDocumentController::class, 'accept'])
        ->name('documents.accept');
    Route::patch('/documents/{document}/status', [StudentDocumentController::class, 'changeStatus'])
        ->name('documents.status');
    Route::resource('documents', StudentDocumentController::class)->except('destroy');

    Route::get('/reports', [ReportController::class, 'index'])
        ->can('view-reports')
        ->name('reports.index');

    Route::get('/activity-log', [ActivityLogController::class, 'index'])
        ->can('view-activity-log')
        ->name('activity-log.index');
    Route::get('/activity-log/{activityLog}', [ActivityLogController::class, 'show'])
        ->can('view-activity-log')
        ->name('activity-log.show');

    Route::middleware('role:admin')->group(function (): void {
        Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus'])
            ->name('users.status');
        Route::put('/users/{user}/password', [UserController::class, 'resetPassword'])
            ->name('users.password');
        Route::resource('users', UserController::class)->except(['show', 'destroy']);

        Route::patch(
            '/document-types/{document_type}/status',
            [DocumentTypeController::class, 'toggleStatus'],
        )->name('document-types.status');
        Route::resource('document-types', DocumentTypeController::class)
            ->parameters(['document-types' => 'document_type'])
            ->except(['show', 'destroy']);
    });
});
