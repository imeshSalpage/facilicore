<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BookingApprovalController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingTemplateController;
use App\Http\Controllers\CompositeBookingController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PredictiveMaintenanceController;
use App\Http\Controllers\PriorityConfigController;
use App\Http\Controllers\ResourceCategoryController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SectorConfigController;
use App\Http\Controllers\TenantSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->load('tenant');
    });

    Route::apiResource('facilities', FacilityController::class);
    Route::apiResource('resource-categories', ResourceCategoryController::class);
    Route::apiResource('resources', ResourceController::class);

    // Bookings — calendar slots + CRUD + cancel action
    Route::get('bookings/slots', [BookingController::class, 'slots']);
    Route::apiResource('bookings', BookingController::class)->except(['destroy']);
    Route::patch('bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    // Supervisor-only — booking approval queue
    Route::middleware('role:supervisor')->group(function () {
        Route::get('approvals/pending', [BookingApprovalController::class, 'pending']);
        Route::patch('approvals/{booking}/approve', [BookingApprovalController::class, 'approve']);
        Route::patch('approvals/{booking}/reject', [BookingApprovalController::class, 'reject']);
    });

    // Admin-only — facility/resource/category management + maintenance write
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('facilities', FacilityController::class)->except(['index', 'show']);
        Route::apiResource('resource-categories', ResourceCategoryController::class)->except(['index', 'show']);
        Route::apiResource('resources', ResourceController::class)->except(['index', 'show']);

        // Maintenance management (create, update, action transitions)
        Route::apiResource('maintenance', MaintenanceController::class)->except(['destroy']);
        Route::patch('maintenance/{maintenanceOrder}/start', [MaintenanceController::class, 'startWork']);
        Route::patch('maintenance/{maintenanceOrder}/complete', [MaintenanceController::class, 'complete']);
        Route::patch('maintenance/{maintenanceOrder}/cancel', [MaintenanceController::class, 'cancel']);

        // Priority engine config setting weights
        Route::get('priority-config', [PriorityConfigController::class, 'index']);
        Route::put('priority-config', [PriorityConfigController::class, 'update']);

        // Booking templates write
        Route::post('booking-templates', [BookingTemplateController::class, 'store']);

        // Predictive Maintenance anomaly scanner
        Route::post('predictive-maintenance/scan', [PredictiveMaintenanceController::class, 'scan']);

        // Advanced Workspace Settings & Role Management
        Route::get('tenant/settings', [TenantSettingsController::class, 'getSettings']);
        Route::put('tenant/settings', [TenantSettingsController::class, 'updateSettings']);
        Route::get('tenant/users', [TenantSettingsController::class, 'getUsers']);
        Route::patch('tenant/users/{user}/approve', [TenantSettingsController::class, 'approveUser']);
        Route::patch('tenant/users/{user}/reject', [TenantSettingsController::class, 'rejectUser']);
        Route::patch('tenant/users/{user}/role', [TenantSettingsController::class, 'updateUserRole']);
        Route::post('tenant/sync-templates', [TenantSettingsController::class, 'syncTemplates']);
    });

    // Supervisor — read maintenance orders, analytics, audit logs, forecasts
    Route::middleware('role:supervisor')->group(function () {
        Route::get('maintenance', [MaintenanceController::class, 'index']);
        Route::get('maintenance/{maintenanceOrder}', [MaintenanceController::class, 'show']);
        Route::get('analytics/report', [AnalyticsController::class, 'report']);
        Route::get('audit-logs', [AuditLogController::class, 'index']);
        Route::get('forecast/resource/{resource}', [ForecastController::class, 'show']);
    });

    // Notifications endpoints
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Booking templates read
    Route::get('booking-templates', [BookingTemplateController::class, 'index']);
    Route::get('booking-templates/{bookingTemplate}', [BookingTemplateController::class, 'show']);

    // Composite Bookings
    Route::get('composite-bookings', [CompositeBookingController::class, 'index']);
    Route::post('composite-bookings', [CompositeBookingController::class, 'store']);
    Route::get('composite-bookings/{compositeBooking}', [CompositeBookingController::class, 'show']);
    Route::patch('composite-bookings/{compositeBooking}/cancel', [CompositeBookingController::class, 'cancel']);

    // Sector Config terminology translation
    Route::get('sector/config', [SectorConfigController::class, 'terminology']);
});

// Read-only resource/facility endpoints available to all authenticated users
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('facilities', [FacilityController::class, 'index']);
    Route::get('facilities/{facility}', [FacilityController::class, 'show']);
    Route::get('resource-categories', [ResourceCategoryController::class, 'index']);
    Route::get('resource-categories/{resourceCategory}', [ResourceCategoryController::class, 'show']);
    Route::get('resources', [ResourceController::class, 'index']);
    Route::get('resources/{resource}', [ResourceController::class, 'show']);
});

Route::get('/tenant-public-info', function () {
    if (!app()->bound(App\Models\Tenant::class)) {
        return response()->json([
            'is_central' => true,
            'name' => 'Facilicore SaaS',
        ]);
    }

    $tenant = app(App\Models\Tenant::class);

    // Dynamic scoping automatically applies
    $facilities = App\Models\Facility::all();
    $resources = App\Models\Resource::with(['category', 'facility'])->where('status', 'active')->get();

    return response()->json([
        'is_central' => false,
        'name' => $tenant->name,
        'subdomain' => $tenant->subdomain,
        'sector' => $tenant->sector ?? 'university',
        'facilities' => $facilities,
        'resources' => $resources,
    ]);
});
