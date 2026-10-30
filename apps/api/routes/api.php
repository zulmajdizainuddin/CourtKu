<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\V1\Admin\VenueController as AdminVenueController;
use App\Http\Controllers\V1\AvailabilityController;
use App\Http\Controllers\V1\BookingController;
use App\Http\Controllers\V1\InviteController;
use App\Http\Controllers\V1\Manage\CourtController;
use App\Http\Controllers\V1\Manage\OpeningHoursController;
use App\Http\Controllers\V1\Manage\PriceRuleController;
use App\Http\Controllers\V1\Manage\StaffController;
use App\Http\Controllers\V1\Manage\VenueClosureController;
use App\Http\Controllers\V1\Manage\VenueController as ManageVenueController;
use App\Http\Controllers\V1\Manage\VenuePhotoController;
use App\Http\Controllers\V1\MeController;
use App\Http\Controllers\V1\PublicVenueController;
use Illuminate\Support\Facades\Route;

// Used by the uptime monitor (NFR-03). Not versioned: it is for machines, not apps.
Route::get('/health', HealthController::class);

Route::prefix('v1')->group(function () {
    // Public: anyone can read an approved venue's page (plan: public reads 60 a minute).
    // TODO(M5): a key of its own for the web app's server, which fetches from one IP for everyone.
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/venues/{venue:slug}', [PublicVenueController::class, 'show']);
        Route::get('/venues/{venue:slug}/courts', [PublicVenueController::class, 'courts']);
        Route::get('/venues/{venue:slug}/availability', [AvailabilityController::class, 'show']);
        Route::post('/venues/{venue:slug}/quote', [AvailabilityController::class, 'quote']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', MeController::class);

        // Players book online (FR-15): verified email first (FR-35), at most 10 tries a minute each.
        Route::post('/venues/{venue:slug}/bookings', [BookingController::class, 'store'])->middleware(['verified', 'throttle:10,1']);
        Route::get('/bookings/{code}', [BookingController::class, 'show'])->middleware('throttle:60,1');

        // The invited person's side of a staff invite (FR-05). Limited, so codes can't be guessed.
        Route::middleware('throttle:10,1')->group(function () {
            Route::get('/invites/{token}', [InviteController::class, 'show']);
            Route::post('/invites/{token}/accept', [InviteController::class, 'accept'])->middleware('verified');
        });

        // Owners and staff. Every route with a {venue} checks membership through
        // VenuePolicy (ADR-002), and tests/Feature/TenantIsolationTest.php proves it.
        // Owners must have two-factor login on (RequireTwoFactor); staff don't have to.
        Route::prefix('manage')->middleware(['verified', 'two-factor'])->group(function () {
            Route::get('/venues', [ManageVenueController::class, 'index']);
            // Listing a venue makes you its owner, so two-factor comes first.
            Route::post('/venues', [ManageVenueController::class, 'store'])->middleware('two-factor:always');
            Route::get('/venues/{venue}', [ManageVenueController::class, 'show'])->can('view', 'venue');
            Route::put('/venues/{venue}', [ManageVenueController::class, 'update'])->can('update', 'venue');
            Route::put('/venues/{venue}/settings', [ManageVenueController::class, 'updateSettings'])->can('update', 'venue');

            Route::get('/venues/{venue}/hours', [OpeningHoursController::class, 'index'])->can('view', 'venue');
            Route::put('/venues/{venue}/hours', [OpeningHoursController::class, 'update'])->can('update', 'venue');

            Route::get('/venues/{venue}/closures', [VenueClosureController::class, 'index'])->can('view', 'venue');
            Route::post('/venues/{venue}/closures', [VenueClosureController::class, 'store'])->can('update', 'venue');
            // scopeBindings: {closure} must belong to {venue}, otherwise 404.
            Route::delete('/venues/{venue}/closures/{closure}', [VenueClosureController::class, 'destroy'])
                ->scopeBindings()
                ->can('update', 'venue');

            Route::get('/venues/{venue}/courts', [CourtController::class, 'index'])->can('view', 'venue');
            Route::post('/venues/{venue}/courts', [CourtController::class, 'store'])->can('update', 'venue');
            Route::put('/venues/{venue}/courts/order', [CourtController::class, 'reorder'])->can('update', 'venue');
            // whereNumber: "order" above is never mistaken for a court id.
            Route::put('/venues/{venue}/courts/{court}', [CourtController::class, 'update'])
                ->whereNumber('court')
                ->scopeBindings()
                ->can('update', 'venue');
            Route::delete('/venues/{venue}/courts/{court}', [CourtController::class, 'destroy'])
                ->whereNumber('court')
                ->scopeBindings()
                ->can('update', 'venue');

            // Peak and off-peak prices (FR-10).
            Route::get('/venues/{venue}/price-rules', [PriceRuleController::class, 'index'])->can('view', 'venue');
            Route::post('/venues/{venue}/price-rules', [PriceRuleController::class, 'store'])->can('update', 'venue');
            Route::put('/venues/{venue}/price-rules/{priceRule}', [PriceRuleController::class, 'update'])
                ->whereNumber('priceRule')
                ->scopeBindings()
                ->can('update', 'venue');
            Route::delete('/venues/{venue}/price-rules/{priceRule}', [PriceRuleController::class, 'destroy'])
                ->whereNumber('priceRule')
                ->scopeBindings()
                ->can('update', 'venue');

            // Staff (FR-05): owners only, including the list, since it shows people's emails.
            Route::get('/venues/{venue}/staff', [StaffController::class, 'index'])->can('update', 'venue');
            Route::post('/venues/{venue}/staff-invites', [StaffController::class, 'invite'])
                ->middleware('throttle:10,1')
                ->can('update', 'venue');
            Route::delete('/venues/{venue}/staff-invites/{invite}', [StaffController::class, 'cancelInvite'])
                ->whereNumber('invite')
                ->scopeBindings()
                ->can('update', 'venue');
            Route::delete('/venues/{venue}/staff/{user}', [StaffController::class, 'remove'])
                ->whereNumber('user')
                ->can('update', 'venue');

            Route::get('/venues/{venue}/photos', [VenuePhotoController::class, 'index'])->can('view', 'venue');
            // At most 10 uploads a minute per owner: each one is sent on to Cloudinary.
            Route::post('/venues/{venue}/photos', [VenuePhotoController::class, 'store'])
                ->middleware('throttle:10,1')
                ->can('update', 'venue');
            Route::put('/venues/{venue}/photos/order', [VenuePhotoController::class, 'reorder'])->can('update', 'venue');
            Route::delete('/venues/{venue}/photos/{photo}', [VenuePhotoController::class, 'destroy'])
                ->whereNumber('photo')
                ->scopeBindings()
                ->can('update', 'venue');
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
