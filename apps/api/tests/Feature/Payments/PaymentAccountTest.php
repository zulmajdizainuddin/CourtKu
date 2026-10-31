<?php

use App\Enums\BookingSource;
use App\Enums\PaymentMode;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenuePaymentAccount;
use App\Payments\FakeGateway;
use App\Payments\PaymentGateways;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schema;

/*
| The owner's Payments tab (FR-17, FR-39, ADR-009, ADR-024): connect the venue's own Stripe account
| through Stripe Connect, and choose how players pay. Stripe is replaced by FakeGateway, so no test
| reaches the internet.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00'); // Monday 10:00 am in Malaysia
    $this->gateway = new FakeGateway;
    $this->app->instance(PaymentGateways::class, PaymentGateways::fake($this->gateway));
    config(['app.frontend_url' => 'https://courtku.vercel.app']);
});

/** @return array{User, Venue} the owner (with two-factor) and the venue */
function paymentsVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create(['email' => 'owner@example.com']);
    $venue = Venue::factory()->active()->ownedBy($owner)->create(['name' => 'Arena Payments']);

    return [$owner, $venue];
}

/** The owner clicks "Connect with Stripe" and gets Stripe's sign-up link. */
function startConnect(User $owner, Venue $venue)
{
    return test()->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/payment-account/onboarding");
}

it('ADR-024 opens a Stripe account for the venue and sends the owner to Stripe\'s own sign-up pages', function () {
    [$owner, $venue] = paymentsVenue();

    startConnect($owner, $venue)
        ->assertOk()
        ->assertJsonPath('data.url', 'https://connect.example.test/acct_fake_1?return='.urlencode("https://courtku.vercel.app/dashboard/{$venue->id}/payments?stripe=return"));

    $account = VenuePaymentAccount::firstOrFail();
    expect($account->venue_id)->toBe($venue->id)
        ->and($account->account_id)->toBe('acct_fake_1')
        ->and($account->isReady())->toBeFalse() // not until the owner finishes on Stripe
        ->and($account->connected_by)->toBe($owner->id)
        ->and($this->gateway->accounts['acct_fake_1']->name)->toBe('Arena Payments');

    // CourtKu keeps no secret for the venue's account: there is nowhere to put one.
    expect(Schema::getColumnListing('venue_payment_accounts'))->not->toContain('credentials')->not->toContain('secret_key');
});

it('makes one Stripe account per venue: going to Stripe again only makes a fresh link', function () {
    [$owner, $venue] = paymentsVenue();
    startConnect($owner, $venue)->assertOk();

    startConnect($owner, $venue)->assertOk();

    expect(VenuePaymentAccount::count())->toBe(1)
        ->and($this->gateway->accounts)->toHaveCount(1);
});

it('asks Stripe how the account stands when the owner comes back, until it can take payments', function () {
    [$owner, $venue] = paymentsVenue();
    startConnect($owner, $venue)->assertOk();
    $url = "/api/v1/manage/venues/{$venue->id}/payment-account";

    // Back from Stripe without finishing.
    $this->actingAs($owner)->getJson($url)->assertOk()->assertJsonPath('data.ready', false);
    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}")->assertJsonPath('data.payment_connected', false);

    // Finished on Stripe.
    $this->gateway->finishOnboarding('acct_fake_1');
    $this->actingAs($owner)->getJson($url)
        ->assertOk()
        ->assertJsonPath('data.account_id', 'acct_fake_1')
        ->assertJsonPath('data.account_name', 'Arena Payments')
        ->assertJsonPath('data.ready', true)
        ->assertJsonPath('data.is_live', false);
    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}")
        ->assertJsonPath('data.payment_connected', true)
        ->assertJsonPath('data.payment_mode', 'counter') // connecting alone doesn't change how players pay
        ->assertJsonPath('data.hold_minutes', 15);

    // Ready: no need to ask Stripe on every visit any more.
    $this->gateway->down = true;
    $this->actingAs($owner)->getJson($url)->assertOk()->assertJsonPath('data.ready', true);
});

it('shows what it last knew when Stripe can\'t be reached, and says so when starting fails', function () {
    [$owner, $venue] = paymentsVenue();
    $this->gateway->down = true;

    startConnect($owner, $venue)->assertStatus(502)->assertJsonPath('error.code', 'PAYMENT_GATEWAY_ERROR');
    expect(VenuePaymentAccount::count())->toBe(0);

    $this->gateway->down = false;
    startConnect($owner, $venue)->assertOk();
    $this->gateway->down = true;
    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/payment-account")->assertOk()->assertJsonPath('data.ready', false);
});

it('says when online payments aren\'t set up on the server at all', function () {
    [$owner, $venue] = paymentsVenue();
    $this->app->forgetInstance(PaymentGateways::class);
    config(['services.stripe.secret' => '']);

    startConnect($owner, $venue)
        ->assertStatus(502)
        ->assertJsonPath('error.message', 'Online payments are not set up on this CourtKu server yet.');
});

it('ADR-023 never uses a live Stripe key, so the demo can\'t move real money', function () {
    [$owner, $venue] = paymentsVenue();
    $this->app->forgetInstance(PaymentGateways::class);
    config(['services.stripe.secret' => 'sk_live_51AbCdEf']);

    startConnect($owner, $venue)->assertStatus(502);
});

it('ADR-002 keeps payments to the owner: staff get 403, owners of other venues 404', function () {
    [$owner, $venue] = paymentsVenue();
    $staff = User::factory()->create();
    $venue->members()->attach($staff, ['role' => 'staff']);
    $otherOwner = User::factory()->withTwoFactor()->create();
    Venue::factory()->active()->ownedBy($otherOwner)->create();
    startConnect($owner, $venue)->assertOk();

    // Staff work here but don't manage money; someone from another venue doesn't even learn it exists.
    foreach ([[$staff, 403], [$otherOwner, 404]] as [$user, $status]) {
        $this->actingAs($user)->getJson("/api/v1/manage/venues/{$venue->id}/payment-account")->assertStatus($status);
        $this->actingAs($user)->postJson("/api/v1/manage/venues/{$venue->id}/payment-account/onboarding")->assertStatus($status);
        $this->actingAs($user)->deleteJson("/api/v1/manage/venues/{$venue->id}/payment-account")->assertStatus($status);
        $this->actingAs($user)->putJson("/api/v1/manage/venues/{$venue->id}/payment-settings", ['payment_mode' => 'counter', 'hold_minutes' => 15])->assertStatus($status);
    }
    expect(VenuePaymentAccount::count())->toBe(1);
});

it('FR-17 lets the owner choose how players pay; online needs an account Stripe says is ready', function () {
    [$owner, $venue] = paymentsVenue();
    $url = "/api/v1/manage/venues/{$venue->id}/payment-settings";
    $refused = fn () => $this->actingAs($owner)->putJson($url, ['payment_mode' => 'both', 'hold_minutes' => 15])
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.payment_mode.0', 'Connect your Stripe account first, then players can pay online.');

    $refused(); // not connected
    startConnect($owner, $venue)->assertOk();
    $refused(); // connected, but not finished on Stripe

    $this->gateway->finishOnboarding('acct_fake_1');
    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/payment-account"); // back from Stripe
    $this->actingAs($owner)->putJson($url, ['payment_mode' => 'both', 'hold_minutes' => 20])
        ->assertOk()
        ->assertJsonPath('data.payment_mode', 'both')
        ->assertJsonPath('data.hold_minutes', 20);

    $this->actingAs($owner)->putJson($url, ['payment_mode' => 'cheque', 'hold_minutes' => 25])
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.payment_mode.0', 'Choose at the counter, online, or both.')
        ->assertJsonPath('error.details.fields.hold_minutes.0', 'Choose 10, 15, 20 or 30 minutes.');
});

it('disconnects: CourtKu stops using the account and players pay at the counter again', function () {
    [$owner, $venue] = paymentsVenue();
    startConnect($owner, $venue)->assertOk();
    $venue->forceFill(['payment_mode' => PaymentMode::Online])->save();

    $this->actingAs($owner)->deleteJson("/api/v1/manage/venues/{$venue->id}/payment-account")->assertNoContent();

    expect(VenuePaymentAccount::count())->toBe(0)
        ->and($venue->fresh()->payment_mode)->toBe(PaymentMode::Counter)
        ->and($this->gateway->accounts)->toHaveKey('acct_fake_1'); // the Stripe account stays the venue's
    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/payment-account")->assertExactJson(['data' => null]);
    $this->actingAs($owner)->deleteJson("/api/v1/manage/venues/{$venue->id}/payment-account")->assertNotFound();
});

it('refuses to disconnect while a player is paying, until their payment time is over', function () {
    [$owner, $venue] = paymentsVenue();
    startConnect($owner, $venue)->assertOk();
    $venue->operatingHours()->create(['weekday' => 1, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    $court = Court::factory()->for($venue)->offering('badminton')->create(['base_price_sen' => 2000]);
    $player = User::factory()->create();
    $service = new BookingService;
    $service->store($service->prepare($court, CarbonImmutable::parse('2026-11-02T18:00:00+08:00'), 60), BookingSource::Online, player: $player, createdBy: $player, hold: true);

    $this->actingAs($owner)->deleteJson("/api/v1/manage/venues/{$venue->id}/payment-account")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'PAYMENTS_IN_PROGRESS');

    $this->travel(15)->minutes(); // the hold ran out
    $this->actingAs($owner)->deleteJson("/api/v1/manage/venues/{$venue->id}/payment-account")->assertNoContent();
});
