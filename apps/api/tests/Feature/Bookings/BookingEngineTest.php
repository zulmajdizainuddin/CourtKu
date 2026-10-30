<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Exceptions\ApiException;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\PriceRule;
use App\Models\User;
use App\Models\Venue;
use App\Services\BookingService;
use App\Services\BookingStateMachine;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

/*
| Dates used here: Monday 2 Nov 2026 and Friday 6 Nov. Now is Monday 10:00 in Malaysia
| (02:00 UTC). Bookings store real times in UTC: Monday 6:00 pm in Malaysia = 10:00 UTC.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
});

/** An approved venue open Monday 08:00–23:00 and Friday 18:00–02:00, with two badminton courts at RM 20.00 an hour. */
function engineVenue(): Venue
{
    $venue = Venue::factory()->active()->create(['slug' => 'engine-arena']);
    $venue->operatingHours()->createMany([
        ['weekday' => 1, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false],
        ['weekday' => 5, 'opens_at' => '18:00', 'closes_at' => '02:00', 'closes_next_day' => true],
    ]);
    Court::factory()->for($venue)->offering('badminton')->count(2)->sequence(['name' => 'Court 1'], ['name' => 'Court 2'])
        ->create(['base_price_sen' => 2000]);

    return $venue;
}

function court1(Venue $venue): Court
{
    return $venue->courts()->where('name', 'Court 1')->firstOrFail();
}

/** Books through the real engine, like every booking in CourtKu. */
function book(Court $court, string $startsAt, int $minutes = 60, ?User $player = null): Booking
{
    $service = new BookingService;

    return $service->store(
        $service->prepare($court, CarbonImmutable::parse($startsAt), $minutes),
        BookingSource::Online,
        player: $player,
        createdBy: $player,
    );
}

/** The error code an ApiException carries, or null when nothing was thrown. */
function apiErrorCode(Closure $action): ?string
{
    try {
        $action();
    } catch (ApiException $e) {
        return $e->errorCode;
    }

    return null;
}

it('FR-15 books a court with one row per half hour, each at its own price', function () {
    $venue = engineVenue();
    PriceRule::factory()->for($venue)->create(['weekdays' => [1], 'price_sen' => 3000]); // Monday from 6:00 pm
    $player = User::factory()->create();

    // Monday 5:00 pm to 7:00 pm: two normal half hours (RM 10.00), two peak ones (RM 15.00).
    $booking = book(court1($venue), '2026-11-02T17:00:00+08:00', 120, $player);

    expect($booking->code)->toMatch('/^CK-[2-9A-HJKMNP-Z]{5}$/')
        ->and($booking->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->total_sen)->toBe(5000)
        ->and($booking->fresh()->starts_at->toDateTimeString())->toBe('2026-11-02 09:00:00') // UTC
        ->and($booking->fresh()->ends_at->toDateTimeString())->toBe('2026-11-02 11:00:00')
        ->and($booking->slots()->orderBy('slot_start')->pluck('price_sen')->all())->toBe([1000, 1000, 1500, 1500])
        ->and(AuditLog::where('action', 'booking.created')->where('user_id', $player->id)->exists())->toBeTrue();
});

it('keeps the price a booking was made at when prices change later', function () {
    $venue = engineVenue();
    $booking = book(court1($venue), '2026-11-02T18:00:00+08:00');

    court1($venue)->update(['base_price_sen' => 9000]);

    expect($booking->fresh()->total_sen)->toBe(2000);
});

it('ADR-015 files a booking after midnight under Friday, with real UTC times', function () {
    $venue = engineVenue();

    $booking = book(court1($venue), '2026-11-07T00:30:00+08:00')->fresh();

    expect($booking->business_date->toDateString())->toBe('2026-11-06')
        ->and($booking->starts_at->toDateTimeString())->toBe('2026-11-06 16:30:00');
});

it('hides booked half hours from availability and refuses to quote them', function () {
    $venue = engineVenue();
    book(court1($venue), '2026-11-02T18:00:00+08:00');

    $cells = collect($this->getJson('/api/v1/venues/engine-arena/availability?date=2026-11-02')->json('data.courts'))
        ->mapWithKeys(fn ($court) => [$court['name'] => collect($court['cells'])->pluck('available', 'time')]);

    expect($cells['Court 1']['17:30'])->toBeTrue()
        ->and($cells['Court 1']['18:00'])->toBeFalse()
        ->and($cells['Court 1']['18:30'])->toBeFalse()
        ->and($cells['Court 1']['19:00'])->toBeTrue()
        ->and($cells['Court 2']['18:00'])->toBeTrue(); // another court is free

    $this->postJson('/api/v1/venues/engine-arena/quote', [
        'court_id' => court1($venue)->id, 'starts_at' => '2026-11-02T18:30:00+08:00', 'minutes' => 60,
    ])
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'SLOT_TAKEN')
        ->assertJsonPath('error.message', 'Court 1 is already booked at this time. Please choose another time or court.')
        ->assertJsonPath('error.details.slots', ['2026-11-02T18:30:00+08:00']); // the half hour already taken
});

it('refuses a booking that overlaps one already made, even by half an hour', function () {
    $venue = engineVenue();
    book(court1($venue), '2026-11-02T18:00:00+08:00', 120);

    expect(apiErrorCode(fn () => book(court1($venue), '2026-11-02T19:30:00+08:00')))->toBe('SLOT_TAKEN')
        ->and(apiErrorCode(fn () => book(court1($venue), '2026-11-02T20:00:00+08:00')))->toBeNull(); // starts as it ends
});

it('NFR double booking: the database refuses the second of two bookings racing for the same time', function () {
    $venue = engineVenue();
    $service = new BookingService;

    // Two players press Book at the same moment. Both checks run before either booking is
    // saved, so both see the court free: a check in the code alone can't stop this.
    $first = $service->prepare(court1($venue), CarbonImmutable::parse('2026-11-02T18:00:00+08:00'), 60);
    $second = $service->prepare(court1($venue), CarbonImmutable::parse('2026-11-02T18:30:00+08:00'), 60);

    $service->store($first, BookingSource::Online);

    // The second shares the 6:30 pm half hour. The unique index refuses it, and says which half hour…
    try {
        $service->store($second, BookingSource::Online);
        $this->fail('The second booking should have been refused.');
    } catch (ApiException $e) {
        expect($e->errorCode)->toBe('SLOT_TAKEN')
            ->and($e->status)->toBe(409)
            ->and($e->details)->toBe(['slots' => ['2026-11-02T18:30:00+08:00']]);
    }

    // …and nothing of it is left behind: no booking, no half hours, no audit entry.
    expect(Booking::count())->toBe(1)
        ->and(BookingSlot::count())->toBe(2)
        ->and(AuditLog::count())->toBe(1);
});

it('ADR-010 lets the database itself refuse a second claim on a half hour', function () {
    $venue = engineVenue();
    $booking = book(court1($venue), '2026-11-02T18:00:00+08:00');

    expect(fn () => BookingSlot::create([
        'court_id' => court1($venue)->id, 'slot_start' => '2026-11-02 10:00:00', 'booking_id' => $booking->id,
    ]))->toThrow(UniqueConstraintViolationException::class);

    // A half hour must belong to exactly one booking or one block.
    expect(fn () => BookingSlot::create(['court_id' => court1($venue)->id, 'slot_start' => '2026-11-02 12:00:00']))
        ->toThrow(QueryException::class);
});

it('frees the half hours of a cancelled booking and records who cancelled and why', function () {
    $venue = engineVenue();
    $staff = User::factory()->create();
    $booking = book(court1($venue), '2026-11-02T18:00:00+08:00');

    $cancelled = (new BookingStateMachine)->transition($booking, BookingStatus::Cancelled, $staff, 'Rain');

    expect($cancelled->status)->toBe(BookingStatus::Cancelled)
        ->and($cancelled->cancelled_at)->not->toBeNull()
        ->and($cancelled->cancel_reason)->toBe('Rain')
        ->and(BookingSlot::count())->toBe(0)
        ->and(AuditLog::where('action', 'booking.cancelled')->where('user_id', $staff->id)->first()->changes)
        ->toEqualCanonicalizing(['from' => 'confirmed', 'to' => 'cancelled', 'reason' => 'Rain']); // MySQL JSON reorders keys

    // The time can be booked again.
    expect(apiErrorCode(fn () => book(court1($venue), '2026-11-02T18:00:00+08:00')))->toBeNull();
});

it('moves a booking only along the allowed steps', function () {
    $venue = engineVenue();
    $machine = new BookingStateMachine;

    $played = book(court1($venue), '2026-11-02T18:00:00+08:00');
    $machine->transition($played, BookingStatus::CheckedIn, null);
    expect($machine->transition($played, BookingStatus::Completed, null)->status)->toBe(BookingStatus::Completed);

    $missed = book(court1($venue), '2026-11-02T20:00:00+08:00');
    $machine->transition($missed, BookingStatus::NoShow, null);
    expect(BookingSlot::where('booking_id', $missed->id)->count())->toBe(2); // kept as history

    $cancelled = book(court1($venue), '2026-11-02T21:00:00+08:00');
    $machine->transition($cancelled, BookingStatus::Cancelled, null);

    expect(fn () => $machine->transition($cancelled, BookingStatus::CheckedIn, null))
        ->toThrow(ApiException::class, "This booking is cancelled, so it can't be marked checked in.")
        ->and(apiErrorCode(fn () => $machine->transition($played, BookingStatus::Cancelled, null)))->toBe('INVALID_BOOKING_STATUS');
});

it('keeps booking history: a court that was booked can\'t be deleted', function () {
    $owner = User::factory()->withTwoFactor()->create();
    $venue = engineVenue();
    $venue->members()->attach($owner, ['role' => 'owner']);
    book(court1($venue), '2026-11-02T18:00:00+08:00');

    $this->actingAs($owner)->deleteJson("/api/v1/manage/venues/{$venue->id}/courts/".court1($venue)->id)
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'COURT_HAS_BOOKINGS');

    // A court never booked can still be deleted.
    $court2 = $venue->courts()->where('name', 'Court 2')->first();
    $this->actingAs($owner)->deleteJson("/api/v1/manage/venues/{$venue->id}/courts/{$court2->id}")->assertNoContent();
});

it('plan step 1: the engine itself checks the venue, the court, the start and the length', function (Closure $change, string $expected) {
    $venue = engineVenue();
    $court = court1($venue);
    // A change either returns another start and length, or changes the venue or court and keeps 6:00 pm for 1 hour.
    $booking = $change($venue, $court);
    [$startsAt, $minutes] = is_array($booking) ? $booking : ['2026-11-02T18:00:00+08:00', 60];

    try {
        (new BookingService)->prepare($court->fresh(), CarbonImmutable::parse($startsAt), $minutes);
        $this->fail('The booking should have been refused.');
    } catch (ApiException $e) {
        expect($e->errorCode)->toBe($expected);
    } catch (ValidationException $e) {
        expect(collect($e->errors())->flatten()->first())->toBe($expected);
    }
})->with([
    'a venue not approved' => [fn (Venue $v) => $v->suspend(), 'VENUE_NOT_ACTIVE'],
    'a court players cannot book' => [fn ($v, Court $c) => $c->update(['is_active' => false]), "This court can't be booked."],
    'a court without a price' => [fn ($v, Court $c) => $c->update(['base_price_sen' => null]), "This court can't be booked."],
    // (A court of another venue can't reach the engine: it takes the venue from the court itself.
    // The /venues/{slug}/... addresses refuse other venues' courts, tested in AvailabilityTest.)
    'not on :00 or :30' => [fn () => ['2026-11-02T18:15:00+08:00', 60], 'Choose a time on the hour or half hour.'],
    'seconds past the half hour' => [fn () => ['2026-11-02T18:00:30+08:00', 60], 'Choose a time on the hour or half hour.'],
    'not whole slots' => [fn () => ['2026-11-02T18:00:00+08:00', 90], 'Bookings here are 60 to 180 minutes long, in 60-minute steps.'],
    'longer than allowed' => [fn () => ['2026-11-02T12:00:00+08:00', 240], 'Bookings here are 60 to 180 minutes long, in 60-minute steps.'],
]);
