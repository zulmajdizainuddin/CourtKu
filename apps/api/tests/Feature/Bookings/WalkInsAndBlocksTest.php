<?php

use App\Enums\BookingSource;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\SlotBlock;
use App\Models\User;
use App\Models\Venue;
use App\Services\BookingService;
use Carbon\CarbonImmutable;

/*
| Now is Monday 2 Nov 2026, 10:10 in Malaysia (02:10 UTC).
| The venue is open Monday 08:00–23:00 and Friday 18:00–02:00.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:10:00');
});

/** @return array{User, User, Venue} the owner (with two-factor), a staff member and the venue */
function counterVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();
    $staff = User::factory()->create(['name' => 'Staff One']);
    $venue = Venue::factory()->active()->ownedBy($owner)->create(['slug' => 'counter-arena']);
    $venue->members()->attach($staff, ['role' => 'staff']);
    $venue->operatingHours()->createMany([
        ['weekday' => 1, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false],
        ['weekday' => 5, 'opens_at' => '18:00', 'closes_at' => '02:00', 'closes_next_day' => true],
    ]);
    Court::factory()->for($venue)->offering('badminton')->count(2)->sequence(['name' => 'Court 1'], ['name' => 'Court 2'])
        ->create(['base_price_sen' => 2000]);

    return [$owner, $staff, $venue];
}

function counterCourt(Venue $venue, string $name = 'Court 1'): Court
{
    return $venue->courts()->where('name', $name)->firstOrFail();
}

function walkIn(Venue $venue, array $input = []): array
{
    return [
        'court_id' => counterCourt($venue)->id,
        'sport' => 'badminton',
        'starts_at' => '2026-11-02T18:00:00+08:00',
        'minutes' => 60,
        'source' => 'walk_in',
        'customer_name' => 'Ali bin Abu',
        'customer_phone' => '012-345 6789',
        ...$input,
    ];
}

function blockInput(Venue $venue, array $input = []): array
{
    return [
        'court_ids' => [counterCourt($venue)->id, counterCourt($venue, 'Court 2')->id],
        'starts_at' => '2026-11-02T18:00:00+08:00',
        'minutes' => 120,
        'reason' => 'Floor repair',
        ...$input,
    ];
}

it('FR-20 lets staff book a walk-in, paid at the counter', function () {
    [, $staff, $venue] = counterVenue();

    $this->actingAs($staff)->postJson("/api/v1/manage/venues/{$venue->id}/bookings", walkIn($venue))
        ->assertCreated()
        ->assertJsonPath('data.source', 'walk_in')
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.total', 'RM 20.00')
        ->assertJsonPath('data.customer', ['name' => 'Ali bin Abu', 'phone' => '+60123456789', 'email' => null]);

    $booking = Booking::sole();
    expect($booking->user_id)->toBeNull()
        ->and($booking->source)->toBe(BookingSource::WalkIn)
        ->and($booking->created_by)->toBe($staff->id);

    // On the schedule, and gone for players.
    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2026-11-02")
        ->assertJsonPath('data.bookings.0.customer.name', 'Ali bin Abu');
    $cells = collect($this->getJson('/api/v1/venues/counter-arena/availability?date=2026-11-02&sport=badminton')->json('data.courts.0.cells'));
    expect($cells->firstWhere('time', '18:00')['available'])->toBeFalse();
});

it('FR-20 lets the owner take a phone booking', function () {
    [$owner, , $venue] = counterVenue();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/bookings", walkIn($venue, ['source' => 'phone']))
        ->assertCreated()
        ->assertJsonPath('data.source', 'phone');
});

it('needs the customer\'s name and a Malaysian phone number, and the usual booking rules', function (Closure $input, string $field, string $message) {
    [, $staff, $venue] = counterVenue();

    $this->actingAs($staff)->postJson("/api/v1/manage/venues/{$venue->id}/bookings", walkIn($venue, $input($venue)))
        ->assertStatus(422)
        ->assertJsonPath("error.details.fields.{$field}.0", $message);
})->with([
    'no name' => [fn () => ['customer_name' => ''], 'customer_name', "Enter the customer's name."],
    'no phone' => [fn () => ['customer_phone' => ''], 'customer_phone', "Enter the customer's phone number."],
    'not a Malaysian phone' => [fn () => ['customer_phone' => '12345'], 'customer_phone', 'Enter a Malaysian phone number, e.g. 012-345 6789.'],
    'online is not for staff' => [fn () => ['source' => 'online'], 'source', 'Choose walk-in or phone.'],
    'a sport the court has no' => [fn () => ['sport' => 'futsal'], 'sport', "Court 1 isn't for futsal."],
    'another venue\'s court' => [fn () => ['court_id' => Court::factory()->for(Venue::factory()->active())->create()->id], 'court_id', "This court can't be booked."],
    // The user's choice: staff follow the same time rules as players.
    'a time that has started' => [fn () => ['starts_at' => '2026-11-02T10:00:00+08:00'], 'starts_at', 'That time has already started. Choose a later time.'],
]);

it('never double-books: a walk-in can\'t take a time a player booked online', function () {
    [, $staff, $venue] = counterVenue();
    $service = new BookingService;
    $service->store($service->prepare(counterCourt($venue), CarbonImmutable::parse('2026-11-02T18:00:00+08:00'), 60), BookingSource::Online, player: User::factory()->create());

    $this->actingAs($staff)->postJson("/api/v1/manage/venues/{$venue->id}/bookings", walkIn($venue, ['starts_at' => '2026-11-02T18:30:00+08:00']))
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'SLOT_TAKEN');
});

it('FR-21 blocks several courts at once, so players can\'t book them', function () {
    [$owner, , $venue] = counterVenue();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/blocks", blockInput($venue))
        ->assertCreated()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.reason', 'Floor repair')
        ->assertJsonPath('data.0.minutes', 120);

    expect(SlotBlock::count())->toBe(2)
        ->and(BookingSlot::whereNotNull('slot_block_id')->count())->toBe(8); // 2 courts x 4 half hours

    $schedule = $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2026-11-02")
        ->assertJsonCount(2, 'data.blocks')
        ->assertJsonPath('data.blocks.0.starts_at', '2026-11-02T18:00:00+08:00');
    expect($schedule->json('data.blocks.0.created_by'))->toBe($owner->name);

    $this->actingAs(User::factory()->create())->withHeaders(['Idempotency-Key' => 'k1'])
        ->postJson('/api/v1/venues/counter-arena/bookings', [
            'court_id' => counterCourt($venue, 'Court 2')->id, 'sport' => 'badminton', 'starts_at' => '2026-11-02T19:00:00+08:00', 'minutes' => 60,
        ])
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'SLOT_TAKEN');
});

it('refuses a block over a booking, names it, and blocks nothing', function () {
    [$owner, $staff, $venue] = counterVenue();
    $this->actingAs($staff)->postJson("/api/v1/manage/venues/{$venue->id}/bookings", walkIn($venue, ['starts_at' => '2026-11-02T18:30:00+08:00']))
        ->assertCreated();
    $booking = Booking::sole();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/blocks", blockInput($venue))
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'SLOT_TAKEN')
        ->assertJsonPath('error.message', "Court 1 at 6:30 pm is booked ({$booking->code}). Cancel or move it first, or choose another time.")
        ->assertJsonPath('error.details.slots.0', ['court' => 'Court 1', 'starts_at' => '2026-11-02T18:30:00+08:00', 'booking' => $booking->code]);

    // All or nothing: Court 2 wasn't blocked either.
    expect(SlotBlock::count())->toBe(0);
});

it('checks the block\'s courts and time', function (Closure $input, string $field, string $message) {
    [$owner, , $venue] = counterVenue();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/blocks", blockInput($venue, $input($venue)))
        ->assertStatus(422)
        ->assertJsonPath("error.details.fields.{$field}.0", $message);
})->with([
    'no courts' => [fn () => ['court_ids' => []], 'court_ids', 'Choose at least one court.'],
    'another venue\'s court' => [fn () => ['court_ids' => [Court::factory()->for(Venue::factory())->create()->id]], 'court_ids', "Choose this venue's courts."],
    'no reason' => [fn () => ['reason' => ''], 'reason', 'Say why, e.g. floor repair or private event.'],
    'not on the half hour' => [fn () => ['starts_at' => '2026-11-02T18:15:00+08:00'], 'starts_at', 'Choose a time on the hour or half hour.'],
    'already over' => [fn () => ['starts_at' => '2026-11-02T09:30:00+08:00', 'minutes' => 30], 'starts_at', 'That time has already passed.'],
    'when the venue is closed' => [fn () => ['starts_at' => '2026-11-03T18:00:00+08:00'], 'starts_at', 'The venue is closed at that time, so there is nothing to block.'],
    'past closing' => [fn () => ['starts_at' => '2026-11-02T22:00:00+08:00'], 'minutes', 'The venue closes at 11:00 pm. Choose a shorter time.'],
]);

it('can block the half hour that is running now, e.g. a leak found at 10:10 am', function () {
    [, $staff, $venue] = counterVenue();

    $this->actingAs($staff)->postJson("/api/v1/manage/venues/{$venue->id}/blocks", blockInput($venue, ['starts_at' => '2026-11-02T10:00:00+08:00', 'minutes' => 60]))
        ->assertCreated();
});

it('ADR-015 puts a block after midnight on the night it belongs to', function () {
    [$owner, , $venue] = counterVenue();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/blocks", blockInput($venue, ['starts_at' => '2026-11-07T00:30:00+08:00', 'minutes' => 60]))
        ->assertCreated();

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2026-11-06")->assertJsonCount(2, 'data.blocks');
    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2026-11-07")->assertJsonCount(0, 'data.blocks');
});

it('lifts a block, and the time can be booked again', function () {
    [, $staff, $venue] = counterVenue();
    $blockId = $this->actingAs($staff)->postJson("/api/v1/manage/venues/{$venue->id}/blocks", blockInput($venue, ['court_ids' => [counterCourt($venue)->id]]))
        ->json('data.0.id');

    $this->actingAs($staff)->deleteJson("/api/v1/manage/venues/{$venue->id}/blocks/{$blockId}")->assertNoContent();

    expect(SlotBlock::count())->toBe(0)->and(BookingSlot::count())->toBe(0);
    $this->actingAs($staff)->postJson("/api/v1/manage/venues/{$venue->id}/bookings", walkIn($venue))->assertCreated();
});
