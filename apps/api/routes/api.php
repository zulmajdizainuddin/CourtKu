<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\TickController;
use App\Http\Controllers\V1\Admin\StatsController;
use App\Http\Controllers\V1\Admin\VenueController as AdminVenueController;
use App\Http\Controllers\V1\AvailabilityController;
use App\Http\Controllers\V1\BookingController;
use App\Http\Controllers\V1\InviteController;
use App\Http\Controllers\V1\Manage\BookingActionController;
use App\Http\Controllers\V1\Manage\CounterBookingController;
use App\Http\Controllers\V1\Manage\CourtController;
use App\Http\Controllers\V1\Manage\OpeningHoursController;
use App\Http\Controllers\V1\Manage\PaymentAccountController;
use App\Http\Controllers\V1\Manage\PriceRuleController;
use App\Http\Controllers\V1\Manage\RefundController;
use App\Http\Controllers\V1\Manage\ReportController;
use App\Http\Controllers\V1\Manage\ScheduleController;
use App\Http\Controllers\V1\Manage\SlotBlockController;
use App\Http\Controllers\V1\Manage\StaffController;
use App\Http\Controllers\V1\Manage\VenueClosureController;
use App\Http\Controllers\V1\Manage\VenueController as ManageVenueController;
use App\Http\Controllers\V1\Manage\VenuePhotoController;
use App\Http\Controllers\V1\MeController;
use App\Http\Controllers\V1\PaymentWebhookController;
use App\Http\Controllers\V1\PublicVenueController;
use Illuminate\Support\Facades\Route;

// Used by the uptime monitor (NFR-03). Not versioned: it is for machines, not apps.
Route::get('/health', HealthController::class);
// The every-minute jobs, called by a cron service on hosting without a scheduler (ADR-022).
Route::post('/tick', TickController::class)->middleware('throttle:10,1');

Route::prefix('v1')->group(function () {
    // Stripe tells CourtKu about payments (ADR-006, ADR-024). No login: the signature proves it is Stripe.
    Route::post('/webhooks/payments/stripe', [PaymentWebhookController::class, 'stripe'])->middleware('throttle:120,1');

    // Public: anyone can read an approved venue's page (plan: public reads 60 a minute each;
    // the web app's own server has a bucket of its own, see AppServiceProvider).
    Route::middleware('throttle:public-reads')->group(function () {
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
        // The player's own bookings (FR-30), and cancelling one (FR-24).
        Route::get('/me/bookings', [BookingController::class, 'index'])->middleware('throttle:60,1');
        Route::post('/bookings/{code}/cancel', [BookingController::class, 'cancel'])->middleware('throttle:10,1');
        // Pay online for a held booking (FR-22): the gateway's payment page, opened or reused.
        Route::post('/bookings/{code}/pay', [BookingController::class, 'pay'])->middleware('throttle:10,1');

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

            // Payments (FR-17, FR-39): the venue's own Stripe account and how players pay. Owners only.
            Route::get('/venues/{venue}/payment-account', [PaymentAccountController::class, 'show'])->can('update', 'venue');
            Route::post('/venues/{venue}/payment-account/onboarding', [PaymentAccountController::class, 'onboarding'])
                ->middleware('throttle:10,1')
                ->can('update', 'venue');
            Route::delete('/venues/{venue}/payment-account', [PaymentAccountController::class, 'destroy'])->can('update', 'venue');
            Route::put('/venues/{venue}/payment-settings', [PaymentAccountController::class, 'updateSettings'])->can('update', 'venue');
            Route::get('/venues/{venue}/payments/attention', [PaymentAccountController::class, 'needingAttention'])->can('update', 'venue');

            // Reports (FR-32): totals for a range of days, and every booking as a CSV file. Owners only: it shows money.
            Route::get('/venues/{venue}/reports', [ReportController::class, 'show'])->can('update', 'venue');
            Route::get('/venues/{venue}/reports/bookings.csv', [ReportController::class, 'bookingsCsv'])
                ->middleware('throttle:10,1')
                ->can('update', 'venue');
            // FR-25: the owner records a refund they made in Stripe. scopeBindings: {payment} must be this {venue}'s.
            Route::post('/venues/{venue}/payments/{payment}/refunds', [RefundController::class, 'store'])
                ->whereNumber('payment')
                ->scopeBindings()
                ->can('update', 'venue');

            // The day schedule for the counter (FR-31): owners and staff.
            Route::get('/venues/{venue}/schedule', [ScheduleController::class, 'show'])->can('view', 'venue');
            // FR-14: what the Schedule tab asks every 10 seconds, to know when to reload.
            Route::get('/venues/{venue}/schedule/version', [ScheduleController::class, 'version'])->middleware('throttle:120,1')->can('view', 'venue');
            // Walk-in and phone bookings (FR-20), and blocks for repairs or private events (FR-21).
            Route::post('/venues/{venue}/bookings', [CounterBookingController::class, 'store'])->can('view', 'venue');
            Route::post('/venues/{venue}/blocks', [SlotBlockController::class, 'store'])->can('view', 'venue');
            // At the counter (FR-28): check in, no-show, paid, and cancelling any booking. {booking} is its code,
            // and scopeBindings only finds bookings of this {venue}.
            Route::prefix('/venues/{venue}/bookings/{booking:code}')->scopeBindings()->can('view', 'venue')->group(function () {
                Route::post('/check-in', [BookingActionController::class, 'checkIn']);
                Route::post('/no-show', [BookingActionController::class, 'noShow']);
                Route::post('/paid', [BookingActionController::class, 'markPaid']);
                Route::post('/cancel', [BookingActionController::class, 'cancel']);
            });
            Route::delete('/venues/{venue}/blocks/{slotBlock}', [SlotBlockController::class, 'destroy'])
                ->whereNumber('slotBlock')
                ->scopeBindings()
                ->can('view', 'venue');

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
            // Platform totals (FR-33).
            Route::get('/stats', StatsController::class);
        });
    });
});
