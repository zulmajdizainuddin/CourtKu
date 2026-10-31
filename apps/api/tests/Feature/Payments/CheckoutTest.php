<?php

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenuePaymentAccount;
use App\Payments\FakeGateway;
use App\Payments\PaymentGateways;
use App\Services\Holds;

/*
| Paying online for a booking (FR-16, FR-22, plan section 9, happy path): the booking is held, the
| player pays on the gateway's own page, and CourtKu confirms it when the gateway says it was paid.
| Stripe is replaced by FakeGateway. Now is Monday 2 Nov 2026, 10:00 in Malaysia.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
    $this->gateway = new FakeGateway;
    $this->app->instance(PaymentGateways::class, PaymentGateways::fake($this->gateway));
    config(['app.frontend_url' => 'https://courtku.vercel.app']);
});

/** An approved venue open every day 08:00–23:00, Court 1 at RM 20.00 an hour, taking payments the given way. */
function payVenue(PaymentMode $mode = PaymentMode::Both, bool $accountReady = true): Venue
{
    $venue = Venue::factory()->active()->create(['slug' => 'pay-arena', 'name' => 'Pay Arena', 'payment_mode' => $mode]);
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'base_price_sen' => 2000]);
    (new VenuePaymentAccount)->forceFill([
        'venue_id' => $venue->id, 'gateway' => 'stripe', 'account_id' => 'acct_pay_arena', 'charges_enabled' => $accountReady,
    ])->save();

    return $venue;
}

/** A player books Court 1 on Monday at 6:00 pm for an hour. */
function bookToPay(Venue $venue, User $player, ?string $pay = 'online', string $key = 'press-1')
{
    return test()->actingAs($player)->postJson("/api/v1/venues/{$venue->slug}/bookings", array_filter([
        'court_id' => $venue->courts()->firstOrFail()->id,
        'sport' => 'badminton',
        'starts_at' => '2026-11-02T18:00:00+08:00',
        'minutes' => 60,
        'pay' => $pay,
    ]), ['Idempotency-Key' => $key]);
}

function payFor(User $player, string $code)
{
    return test()->actingAs($player)->postJson("/api/v1/bookings/{$code}/pay");
}

it('FR-17 tells the booking page how players can pay at each venue', function (PaymentMode $mode, bool $ready, bool $online, bool $counter) {
    payVenue($mode, $ready);

    $this->getJson('/api/v1/venues/pay-arena')
        ->assertOk()
        ->assertJsonPath('data.booking.pay_online', $online)
        ->assertJsonPath('data.booking.pay_at_counter', $counter)
        ->assertJsonPath('data.booking.hold_minutes', 15);
})->with([
    'counter only' => [PaymentMode::Counter, true, false, true],
    'both' => [PaymentMode::Both, true, true, true],
    'online only' => [PaymentMode::Online, true, true, false],
    // The owner chose online, but Stripe can't take payments yet: players still get to book, at the counter.
    'online, account not ready' => [PaymentMode::Online, false, false, true],
]);

it('FR-16 holds a booking paid online, and confirms one paid at the venue straight away', function () {
    $venue = payVenue();
    $player = User::factory()->create();

    bookToPay($venue, $player, 'online')
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending_payment')
        ->assertJsonPath('data.hold_expires_at', '2026-11-02T10:15:00+08:00')
        ->assertJsonPath('data.pay_at_venue', false)
        ->assertJsonPath('data.paid', false)
        ->assertJsonPath('data.payment', null);

    $this->travelTo('2026-11-02 02:01:00');
    $this->actingAs($player)->postJson("/api/v1/venues/{$venue->slug}/bookings", [
        'court_id' => $venue->courts()->firstOrFail()->id, 'sport' => 'badminton', 'starts_at' => '2026-11-02T20:00:00+08:00', 'minutes' => 60, 'pay' => 'counter',
    ], ['Idempotency-Key' => 'press-2'])
        ->assertCreated()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.pay_at_venue', true);
});

it('refuses a way of paying the venue doesn\'t take, and picks the venue\'s own when the player doesn\'t say', function () {
    $player = User::factory()->create();

    $counterOnly = payVenue(PaymentMode::Counter);
    bookToPay($counterOnly, $player, 'online')
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.pay.0', 'This venue doesn\'t take online payment. You pay at the counter.');
    bookToPay($counterOnly, $player, null)->assertCreated()->assertJsonPath('data.status', 'confirmed');

    Booking::query()->each(fn (Booking $b) => $b->slots()->delete());
    $counterOnly->forceFill(['payment_mode' => PaymentMode::Online, 'slug' => 'online-only'])->save();
    bookToPay($counterOnly->fresh(), $player, 'counter', 'press-3')
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.pay.0', 'This venue takes online payment only.');
    bookToPay($counterOnly->fresh(), $player, null, 'press-4')->assertCreated()->assertJsonPath('data.status', 'pending_payment');
});

it('FR-22 opens Stripe\'s payment page on the venue\'s own account, for the booking\'s total', function () {
    $venue = payVenue();
    $player = User::factory()->create(['email' => 'siti@example.com']);
    $code = bookToPay($venue, $player)->json('data.code');

    payFor($player, $code)->assertOk()->assertJsonPath('data.url', 'https://pay.example.test/cs_fake_1');

    $payment = Payment::firstOrFail();
    $request = $this->gateway->requests[0];
    expect($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->account_id)->toBe('acct_pay_arena')
        ->and($this->gateway->checkoutAccounts['cs_fake_1'])->toBe('acct_pay_arena') // the money lands with the venue (ADR-024)
        ->and($payment->amount_sen)->toBe(2000)
        ->and($request->amountSen)->toBe(2000)
        ->and($request->bookingCode)->toBe($code)
        ->and($request->customerEmail)->toBe('siti@example.com')
        ->and($request->description)->toBe("Pay Arena: Court 1, Mon 2 Nov, 6:00 pm to 7:00 pm ({$code})")
        ->and($request->returnUrl)->toBe("https://courtku.vercel.app/bookings/{$code}/status")
        ->and($request->expiresAt->toDateTimeString())->toBe('2026-11-02 02:15:00'); // the page closes with the hold
});

it('reuses the open payment page, and opens a new one after a declined card', function () {
    $venue = payVenue();
    $player = User::factory()->create();
    $code = bookToPay($venue, $player)->json('data.code');

    payFor($player, $code)->assertOk();
    payFor($player, $code)->assertOk()->assertJsonPath('data.url', 'https://pay.example.test/cs_fake_1'); // back to the same page
    expect(Payment::count())->toBe(1);

    Payment::firstOrFail()->forceFill(['status' => PaymentStatus::Failed])->save(); // the webhook said the card was declined
    payFor($player, $code)->assertOk()->assertJsonPath('data.url', 'https://pay.example.test/cs_fake_2');
    expect(Payment::count())->toBe(2);
});

it('refuses to open a payment page for a booking that isn\'t waiting for payment', function () {
    $venue = payVenue();
    $player = User::factory()->create();
    $held = bookToPay($venue, $player)->json('data.code');

    payFor(User::factory()->create(), $held)->assertNotFound(); // someone else's booking

    $this->travel(15)->minutes(); // the hold ran out
    payFor($player, $held)->assertStatus(409)->assertJsonPath('error.code', 'NOT_AWAITING_PAYMENT');
    expect(Booking::firstOrFail()->status)->toBe(BookingStatus::Expired);
});

it('says when the venue can\'t take online payment any more, and when Stripe can\'t be reached', function () {
    $venue = payVenue();
    $player = User::factory()->create();
    $code = bookToPay($venue, $player)->json('data.code');

    $this->gateway->down = true;
    payFor($player, $code)->assertStatus(502)->assertJsonPath('error.code', 'PAYMENT_GATEWAY_ERROR');
    expect(Booking::firstOrFail()->status)->toBe(BookingStatus::PendingPayment); // the hold stays: try again

    $venue->paymentAccount->forceFill(['charges_enabled' => false])->save();
    payFor($player, $code)->assertStatus(409)->assertJsonPath('error.code', 'ONLINE_PAYMENT_UNAVAILABLE');
});

it('ADR-006 confirms a payment by asking Stripe when the player comes back, with or without the webhook', function () {
    $venue = payVenue();
    $player = User::factory()->create();
    $code = bookToPay($venue, $player)->json('data.code');
    payFor($player, $code)->assertOk();

    // Back from Stripe without paying: still waiting.
    $this->actingAs($player)->getJson("/api/v1/bookings/{$code}")
        ->assertJsonPath('data.status', 'pending_payment')
        ->assertJsonPath('data.payment.status', 'pending');

    // Paid on Stripe's page. No webhook reaches a laptop, but CourtKu asks Stripe itself.
    $this->gateway->pay('cs_fake_1');
    $this->travel(6)->seconds();
    $this->actingAs($player)->getJson("/api/v1/bookings/{$code}")
        ->assertOk()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.paid', true)
        ->assertJsonPath('data.payment_method', 'online')
        ->assertJsonPath('data.payment.status', 'paid');

    expect(Booking::firstOrFail()->payment_method)->toBe(PaymentMethod::Online);
});

it('asks Stripe at most every few seconds, however often the page refreshes', function () {
    $venue = payVenue();
    $player = User::factory()->create();
    $code = bookToPay($venue, $player)->json('data.code');
    payFor($player, $code)->assertOk();

    foreach (range(1, 4) as $i) {
        $this->actingAs($player)->getJson("/api/v1/bookings/{$code}")->assertOk();
    }
    expect($this->gateway->lookups)->toBe(1);

    $this->travel(6)->seconds();
    $this->actingAs($player)->getJson("/api/v1/bookings/{$code}")->assertOk();
    expect($this->gateway->lookups)->toBe(2);
});

it('ADR-006 never trusts what the browser brings back from the payment page', function () {
    $venue = payVenue();
    $player = User::factory()->create();
    $code = bookToPay($venue, $player)->json('data.code');
    payFor($player, $code)->assertOk();

    // Stripe-like values in the address change nothing: only Stripe's own answer counts.
    $this->actingAs($player)->getJson("/api/v1/bookings/{$code}?paid=true&payment_status=paid&session_id=cs_fake_1")
        ->assertJsonPath('data.status', 'pending_payment');
});

it('plan section 9: a payment that came too late shows as needing a refund, never as "not charged"', function () {
    $venue = payVenue();
    $player = User::factory()->create();
    $code = bookToPay($venue, $player)->json('data.code');
    payFor($player, $code)->assertOk();
    $this->travel(15)->minutes();
    $this->gateway->down = true; // Stripe can't be reached, so the payment page can't be closed...
    (new Holds)->releaseExpired(); // ...but the time still goes back to other players
    $this->gateway->down = false;
    bookToPay($venue, User::factory()->create(), 'counter', 'someone-else')->assertCreated(); // and someone takes it
    $this->gateway->pay('cs_fake_1'); // then the first player pays on the page that stayed open

    $this->actingAs($player)->getJson("/api/v1/bookings/{$code}")
        ->assertJsonPath('data.status', 'expired')
        ->assertJsonPath('data.payment.status', 'paid')
        ->assertJsonPath('data.payment.problem', 'booking_expired'); // the venue refunds it
});

it('confirms a payment that came in just after the hold time, when the player comes back before anyone took the time', function () {
    $venue = payVenue();
    $player = User::factory()->create();
    $code = bookToPay($venue, $player)->json('data.code');
    payFor($player, $code)->assertOk();
    $this->travel(16)->minutes(); // slow at the bank: the hold time is over, but nothing has freed it yet
    $this->gateway->pay('cs_fake_1');

    // CourtKu asks Stripe before freeing holds whose time is up, so the player keeps the court they paid for.
    $this->actingAs($player)->getJson("/api/v1/bookings/{$code}")
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.paid', true);
});
