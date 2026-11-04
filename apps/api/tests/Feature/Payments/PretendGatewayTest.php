<?php

use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenuePaymentAccount;
use App\Payments\PaymentGateways;
use App\Payments\PretendGateway;
use App\Payments\StripeGateway;

/*
| ADR-034: the browser tests' stand-in for Stripe. The player pays on CourtKu's own "pretend Stripe"
| page, then the real code confirms the booking. It must never work on the live site.
| Now is Monday 2 Nov 2026, 10:00 in Malaysia.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
    config(['app.frontend_url' => 'http://localhost:3100', 'services.stripe.secret' => 'sk_test_unused']);
});

function pretendVenue(): Venue
{
    $venue = Venue::factory()->active()->create(['slug' => 'pretend-arena', 'payment_mode' => 'both']);
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'base_price_sen' => 2000]);
    (new VenuePaymentAccount)->forceFill(['venue_id' => $venue->id, 'gateway' => 'pretend', 'account_id' => 'acct_pretend_test', 'charges_enabled' => true])->save();

    return $venue;
}

it('ADR-034 lets a player pay on the pretend page, and confirms the booking when they come back', function () {
    config(['services.payments.gateway' => 'pretend']);
    $venue = pretendVenue();
    $player = User::factory()->create();

    $code = $this->actingAs($player)->postJson('/api/v1/venues/pretend-arena/bookings', [
        'court_id' => $venue->courts()->firstOrFail()->id, 'sport' => 'badminton',
        'starts_at' => '2026-11-02T18:00:00+08:00', 'minutes' => 60, 'pay' => 'online',
    ], ['Idempotency-Key' => 'pretend-1'])->assertCreated()->json('data.code');

    $url = $this->actingAs($player)->postJson("/api/v1/bookings/{$code}/pay")->assertOk()->json('data.url');
    expect($url)->toStartWith('http://localhost:3100/api/pretend-checkout/cs_pretend_');
    $path = (string) parse_url($url, PHP_URL_PATH);

    $this->get($path)->assertOk()->assertSee('Pretend Stripe')->assertSee('RM 20.00');
    $this->get("{$path}/pay")->assertRedirect("http://localhost:3100/bookings/{$code}/status");

    // Coming back, the booking page asks the gateway, which now says it was paid.
    $this->actingAs($player)->getJson("/api/v1/bookings/{$code}")
        ->assertOk()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.paid', true);

    // A closed page can't be paid again.
    $this->get($path)->assertOk()->assertSee('complete')->assertDontSee('/pay"', false);
});

it('ADR-034 is off unless switched on: the page is 404 and Stripe is used', function () {
    $this->get('/api/pretend-checkout/cs_pretend_anything')->assertNotFound();
    expect(app(PaymentGateways::class)->make())->toBeInstanceOf(StripeGateway::class);
});

it('ADR-034 never works on the live site, even when switched on', function () {
    config(['services.payments.gateway' => 'pretend']);
    $this->app->detectEnvironment(fn () => 'production');

    expect(PretendGateway::enabled())->toBeFalse()
        ->and(app(PaymentGateways::class)->make())->toBeInstanceOf(StripeGateway::class);
    $this->get('/api/pretend-checkout/cs_pretend_anything')->assertNotFound();
});
