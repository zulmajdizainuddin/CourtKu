<?php

use App\Models\User;
use App\Models\Venue;
use Database\Seeders\E2eSeeder;
use PragmaRX\Google2FA\Google2FA;

/* ADR-034: the browser tests' data. */

it('loads a bookable venue that takes online payment, and an account of each kind per screen size', function () {
    config(['services.payments.gateway' => 'pretend']);
    $this->seed(E2eSeeder::class);

    $this->getJson('/api/v1/venues/e2e-arena')
        ->assertOk()
        ->assertJsonPath('data.booking.pay_online', true)
        ->assertJsonCount(3, 'data.courts');

    $venue = Venue::where('slug', 'e2e-arena')->firstOrFail();
    foreach (['desktop', 'phone'] as $screen) {
        $owner = User::where('email', "owner-{$screen}@example.com")->firstOrFail();
        expect($venue->members()->whereKey($owner->id)->first()?->pivot->role->value)->toBe('owner')
            ->and(decrypt($owner->two_factor_secret))->toBe(E2eSeeder::OWNER_SECRETS[$screen])
            ->and(app(Google2FA::class)->verifyKey(E2eSeeder::OWNER_SECRETS[$screen], app(Google2FA::class)->getCurrentOtp(E2eSeeder::OWNER_SECRETS[$screen])))->toBeTrue();
        expect(User::where('email', "player-{$screen}@example.com")->firstOrFail()->hasVerifiedEmail())->toBeTrue();
    }
});

it('refuses to load the test accounts on the live site', function () {
    $this->app->detectEnvironment(fn () => 'production');

    // Straight to the seeder: `db:seed` itself would stop to ask "are you sure?" in production.
    expect(fn () => (new E2eSeeder)->run())->toThrow(RuntimeException::class);
    expect(User::where('email', 'like', '%@example.com')->count())->toBe(0);
});
