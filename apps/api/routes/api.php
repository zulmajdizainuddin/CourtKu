<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\V1\Admin\VenueController as AdminVenueController;
use App\Http\Controllers\V1\Manage\VenueController as ManageVenueController;
use App\Http\Controllers\V1\MeController;
use Illuminate\Support\Facades\Route;

// Used by the uptime monitor (NFR-03). Not versioned: it is for machines, not apps.
Route::get('/health', HealthController::class);

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', MeController::class);

        // Owners and staff. Every route with a {venue} checks membership through
        // VenuePolicy (ADR-002), and tests/Feature/TenantIsolationTest.php proves it.
        // Owners must have two-factor login on (RequireTwoFactor); staff don't have to.
        Route::prefix('manage')->middleware(['verified', 'two-factor'])->group(function () {
            Route::get('/venues', [ManageVenueController::class, 'index']);
            // Listing a venue makes you its owner, so two-factor comes first.
            Route::post('/venues', [ManageVenueController::class, 'store'])->middleware('two-factor:always');
            Route::get('/venues/{venue}', [ManageVenueController::class, 'show'])->can('view', 'venue');
        });

        // The platform admin only (is_platform_admin, set with `php artisan courtku:make-admin`),
        // and only with two-factor login on.
        Route::prefix('admin')->middleware(['can:admin', 'two-factor'])->group(function () {
            Route::get('/venues', [AdminVenueController::class, 'index']);
            Route::post('/venues/{venue}/approve', [AdminVenueController::class, 'approve']);
            Route::post('/venues/{venue}/suspend', [AdminVenueController::class, 'suspend']);
        });
    });
});
