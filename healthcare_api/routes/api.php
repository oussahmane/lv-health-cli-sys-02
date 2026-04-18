<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SuperAdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Public Clinic Info & Patient Actions
Route::middleware('clinic.active')->group(function () {
    Route::get('/q/{slug}', [App\Http\Controllers\Api\PublicQueueController::class, 'showClinic']);
    Route::post('/q/{slug}/register', [App\Http\Controllers\Api\PublicQueueController::class, 'register'])->middleware('throttle:10,1');
    Route::get('/q/{slug}/ticket/{id}', [App\Http\Controllers\Api\PublicQueueController::class, 'showTicket']);
    Route::get('/q/{slug}/live', [App\Http\Controllers\Api\PublicQueueController::class, 'liveQueue']);
    Route::get('/q/{slug}/find-ticket', [App\Http\Controllers\Api\PublicQueueController::class, 'findTickets']);
    Route::post('/q/ticket/{id}/cancel', [App\Http\Controllers\Api\PublicQueueController::class, 'cancelTicket']);
});

// Auth Routes
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Super Admin Routes
    Route::middleware('role:super_admin')->prefix('super')->group(function () {
        Route::get('/stats', [SuperAdminController::class, 'stats']);
        Route::get('/clinics', [SuperAdminController::class, 'indexClinics']);
        Route::post('/clinics', [SuperAdminController::class, 'storeClinic']);
        Route::put('/clinics/{clinic}', [SuperAdminController::class, 'updateClinic']);
        Route::patch('/clinics/{clinic}/toggle', [SuperAdminController::class, 'toggleClinicStatus']);
        Route::patch('/users/{user}/toggle', [SuperAdminController::class, 'toggleUserStatus']);

        // Plans Management
        Route::get('/plans', [SuperAdminController::class, 'listPlans']);
        Route::post('/plans', [SuperAdminController::class, 'storePlan']);
        Route::put('/plans/{plan}', [SuperAdminController::class, 'updatePlan']);
        Route::delete('/plans/{plan}', [SuperAdminController::class, 'deletePlan']);

        // Payments Management
        Route::put('/payments/{payment}/approve', [\App\Http\Controllers\Api\PaymentController::class, 'approve']);
        Route::put('/payments/{payment}/reject', [\App\Http\Controllers\Api\PaymentController::class, 'reject']);
    });

    // Clinic Staff Routes (Common + Scoped)
    Route::middleware(['clinic.active'])->prefix('clinic')->group(function () {
        
        // Dashboard (Common access, but content filtered by permission in frontend)
        Route::get('/dashboard', [\App\Http\Controllers\Api\ClinicDashboardController::class, 'index']);
        
        // Queue Management
        Route::middleware('permission:manage_queue')->group(function () {
            Route::post('/queue', [\App\Http\Controllers\Api\ClinicDashboardController::class, 'store']);
            Route::post('/queue/call-next', [\App\Http\Controllers\Api\ClinicDashboardController::class, 'callNext']);
            Route::post('/queue/{queue}/priority', [\App\Http\Controllers\Api\ClinicDashboardController::class, 'updatePriority']);
            Route::post('/queue/reorder', [\App\Http\Controllers\Api\ClinicDashboardController::class, 'reorder']);
            Route::post('/settings/pause', [\App\Http\Controllers\Api\ClinicDashboardController::class, 'togglePause']);
        });

        // Consultation (Doctors & Admins)
        Route::middleware('permission:consult_patients')->group(function () {
            Route::post('/queue/{queue}/status', [\App\Http\Controllers\Api\ClinicDashboardController::class, 'updateStatus']);
        });
        
        // History & Exports
        Route::middleware('permission:view_history')->group(function () {
            Route::get('/history', [\App\Http\Controllers\Api\ClinicHistoryController::class, 'index']);
            Route::get('/history/export', [\App\Http\Controllers\Api\ClinicHistoryController::class, 'export']);
        });
        
        // Analytics
        Route::get('/analytics', [\App\Http\Controllers\Api\ClinicAnalyticsController::class, 'index'])->middleware('permission:view_analytics');
        
        // Payments & Billing
        Route::get('/payments', [\App\Http\Controllers\Api\PaymentController::class, 'index'])->middleware('permission:view_billing');
        Route::post('/payments', [\App\Http\Controllers\Api\PaymentController::class, 'store'])->middleware('permission:view_billing');
        
        // Staff Management
        Route::get('/staff', [\App\Http\Controllers\Api\ClinicStaffController::class, 'index'])->middleware('permission:manage_staff');
        Route::post('/staff', [\App\Http\Controllers\Api\ClinicStaffController::class, 'store'])->middleware(['permission:manage_staff', 'plan.limits']);
        
        // Settings & Counters
        Route::middleware('permission:manage_settings')->group(function () {
            Route::get('/settings', [\App\Http\Controllers\Api\ClinicSettingsController::class, 'index']);
            Route::post('/settings', [\App\Http\Controllers\Api\ClinicSettingsController::class, 'update']);
            Route::apiResource('counters', \App\Http\Controllers\Api\CounterController::class);
        });
    });
});
