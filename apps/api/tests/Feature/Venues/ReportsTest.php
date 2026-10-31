<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use App\Services\BookingService;
use App\Services\BookingStateMachine;
use Carbon\CarbonImmutable;

/*
| FR-32 owner reports and FR-33 admin totals.
| Now is Monday 2 Nov 2026, 10:00 in Malaysia (02:00 UTC). The venue is open 08:00–23:00 (15 hours)
| every day and has two courts at RM 20.00 an hour.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
});

/** @return array{User, User, User, Venue} the owner, a staff member, a player and the venue */
function reportVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();
    $staff = User::factory()->create();
    $player = User::factory()->create(['name' => 'Player Chong', 'email' => 'chong@example.com', 'phone' => '+60123456789']);
    $venue = Venue::factory()->active()->ownedBy($owner)->create(['slug' => 'report-arena']);
    $venue->members()->attach($staff, ['role' => 'staff']);
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->count(2)->sequence(['name' => 'Court 1', 'sort_order' => 1], ['name' => 'Court 2', 'sort_order' => 2])
        ->create(['base_price_sen' => 2000]);

    return [$owner, $staff, $player, $venue];
}

/** A booking through the real engine, e.g. reportBooking($venue, 'Court 1', '2026-11-02T18:00', 120, $player). */
function reportBooking(Venue $venue, string $court, string $startsAt, int $minutes, ?User $player, bool $hold = false): Booking
{
    $service = new BookingService;

    return $service->store(
        $service->prepare($venue->courts()->where('name', $court)->firstOrFail(), CarbonImmutable::parse($startsAt.':00+08:00'), $minutes),
        $player ? BookingSource::Online : BookingSource::WalkIn,
        player: $player,
        sportId: $venue->courts()->where('name', $court)->firstOrFail()->sports()->value('sports.id'),
        customerName: $player ? null : 'Walk-in Wong',
        customerPhone: $player ? null : '+60111111111',
        hold: $hold,
    );
}

/**
 * Monday to Wednesday:
 *   Mon Court 1 18:00 2 h RM 40  player, paid cash at the counter
 *   Mon Court 2 19:00 1 h RM 20  walk-in, paid by card
 *   Tue Court 1 18:00 1 h RM 20  player, paid online (RM 5 refunded later)
 *   Tue Court 1 20:00 1 h RM 20  player, cancelled
 *   Wed Court 2 09:00 1 h RM 20  player, no-show, never paid
 *   Wed Court 1 18:00 1 h RM 20  player, still to pay at the venue
 *   Wed Court 2 20:00 1 h        player, unpaid hold: not counted
 *   Thu Court 1 18:00 1 h RM 20  outside the range
 *
 * @return array<string, Booking>
 */
function reportWeek(Venue $venue, User $player, User $owner): array
{
    $machine = new BookingStateMachine;

    $cash = reportBooking($venue, 'Court 1', '2026-11-02T18:00', 120, $player);
    $machine->markPaid($cash, PaymentMethod::Cash, $owner);

    $walkIn = reportBooking($venue, 'Court 2', '2026-11-02T19:00', 60, null);
    $machine->markPaid($walkIn, PaymentMethod::Card, $owner);

    $online = reportBooking($venue, 'Court 1', '2026-11-03T18:00', 60, $player, hold: true);
    $machine->transition($online, BookingStatus::Confirmed, null, 'Paid online');
    $online->refresh()->forceFill(['paid_at' => now(), 'payment_method' => PaymentMethod::Online])->save();
    (new Payment)->forceFill([
        'booking_id' => $online->id, 'venue_id' => $venue->id, 'gateway' => 'stripe', 'account_id' => 'acct_test', 'checkout_id' => 'cs_test_report',
        'amount_sen' => 2000, 'status' => 'paid', 'paid_amount_sen' => 2000, 'refunded_sen' => 500, 'paid_at' => now(),
    ])->save();

    $cancelled = reportBooking($venue, 'Court 1', '2026-11-03T20:00', 60, $player);
    $machine->transition($cancelled, BookingStatus::Cancelled, $player, 'Cancelled by the player');

    $noShow = reportBooking($venue, 'Court 2', '2026-11-04T09:00', 60, $player);
    $machine->transition($noShow, BookingStatus::NoShow, $owner);

    $unpaid = reportBooking($venue, 'Court 1', '2026-11-04T18:00', 60, $player);
    reportBooking($venue, 'Court 2', '2026-11-04T20:00', 60, $player, hold: true);
    reportBooking($venue, 'Court 1', '2026-11-05T18:00', 60, $player);

    return compact('cash', 'walkIn', 'online', 'cancelled', 'noShow', 'unpaid');
}

function reportUrl(Venue $venue, string $from = '2026-11-02', string $to = '2026-11-04', string $path = ''): string
{
    return "/api/v1/manage/venues/{$venue->id}/reports{$path}?from={$from}&to={$to}";
}

it('FR-32 adds up bookings, money, cancellations and no-shows for a range of days', function () {
    [$owner, , $player, $venue] = reportVenue();
    reportWeek($venue, $player, $owner);

    $this->actingAs($owner)->getJson(reportUrl($venue))
        ->assertOk()
        ->assertJsonPath('data.from', '2026-11-02')
        ->assertJsonPath('data.to', '2026-11-04')
        ->assertJsonPath('data.totals', [
            'bookings' => 5, // cancelled, the unpaid hold and Thursday don't count
            'hours_booked' => 6,
            'booked_sen' => 12000,
            'online_bookings' => 4,
            'counter_bookings' => 1,
            'cancelled' => 1,
            'no_shows' => 1,
            'paid_online_sen' => 2000,
            'paid_at_venue_sen' => 6000, // RM 40 cash + RM 20 card
            'refunded_sen' => 500,
            'collected_sen' => 7500,
            'to_collect_sen' => 2000, // Wednesday's RM 20; the no-show isn't coming
        ]);
});

it('FR-32 gives every day of the range for the chart, quiet days included', function () {
    [$owner, , $player, $venue] = reportVenue();
    reportWeek($venue, $player, $owner);

    $this->actingAs($owner)->getJson(reportUrl($venue, to: '2026-11-06'))
        ->assertJsonPath('data.days', [
            ['date' => '2026-11-02', 'bookings' => 2, 'booked_sen' => 6000],
            ['date' => '2026-11-03', 'bookings' => 1, 'booked_sen' => 2000],
            ['date' => '2026-11-04', 'bookings' => 2, 'booked_sen' => 4000],
            ['date' => '2026-11-05', 'bookings' => 1, 'booked_sen' => 2000],
            ['date' => '2026-11-06', 'bookings' => 0, 'booked_sen' => 0],
        ]);
});

it('FR-32 shows how full each court was: hours booked out of hours open', function () {
    [$owner, , $player, $venue] = reportVenue();
    reportWeek($venue, $player, $owner);

    $this->actingAs($owner)->getJson(reportUrl($venue))
        ->assertJsonPath('data.open_hours_per_court', 45) // 3 days x 15 hours
        ->assertJsonPath('data.courts', [
            ['id' => $venue->courts()->where('name', 'Court 1')->value('id'), 'name' => 'Court 1', 'is_active' => true,
                'bookings' => 3, 'hours_booked' => 4, 'booked_sen' => 8000, 'full_percent' => 9],
            ['id' => $venue->courts()->where('name', 'Court 2')->value('id'), 'name' => 'Court 2', 'is_active' => true,
                'bookings' => 2, 'hours_booked' => 2, 'booked_sen' => 4000, 'full_percent' => 4],
        ]);
});

it('FR-21 leaves closed days out of the hours open', function () {
    [$owner, , , $venue] = reportVenue();
    $venue->closures()->create(['starts_on' => '2026-11-03', 'ends_on' => '2026-11-03', 'reason' => 'Floor polishing']);

    $this->actingAs($owner)->getJson(reportUrl($venue))->assertJsonPath('data.open_hours_per_court', 30);
});

it('only lets the owner see the reports: staff get 403, other venues 404', function () {
    [, $staff, , $venue] = reportVenue();
    $otherOwner = User::factory()->withTwoFactor()->create();
    Venue::factory()->active()->ownedBy($otherOwner)->create();

    $this->actingAs($staff)->getJson(reportUrl($venue))->assertForbidden();
    $this->actingAs($staff)->get(reportUrl($venue, path: '/bookings.csv'))->assertForbidden();
    // Like every other venue page: someone else's venue doesn't exist for them (TenantIsolationTest).
    $this->actingAs($otherOwner)->getJson(reportUrl($venue))->assertNotFound();
});

it('checks the dates: both needed, in order, and at most a year', function () {
    [$owner, , , $venue] = reportVenue();

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/reports")
        ->assertStatus(422)->assertJsonValidationErrors(['from', 'to'], 'error.details.fields');
    $this->actingAs($owner)->getJson(reportUrl($venue, '2026-11-04', '2026-11-02'))
        ->assertStatus(422)->assertJsonPath('error.details.fields.to.0', "The end date can't be before the start date.");
    $this->actingAs($owner)->getJson(reportUrl($venue, '2026-01-01', '2027-01-02'))
        ->assertStatus(422)->assertJsonPath('error.details.fields.to.0', 'Choose at most a year at once.');
    $this->actingAs($owner)->getJson(reportUrl($venue, '2026-01-01', '2026-12-31'))->assertOk();
});

it('FR-32 downloads every booking of the range as a CSV file', function () {
    [$owner, , $player, $venue] = reportVenue();
    $week = reportWeek($venue, $player, $owner);

    $response = $this->actingAs($owner)->get(reportUrl($venue, path: '/bookings.csv'))->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertDownload('courtku-report-arena-bookings-2026-11-02-to-2026-11-04.csv');

    $csv = $response->streamedContent();
    expect($csv)->toStartWith("\xEF\xBB\xBF"); // UTF-8 for Excel
    $rows = array_map(str_getcsv(...), explode("\n", trim(substr($csv, 3))));

    expect($rows[0])->toBe(['Code', 'Date', 'Start', 'End', 'Court', 'Sport', 'Player', 'Phone', 'Email', 'Made', 'Status',
        'Price (RM)', 'Paid', 'Paid how', 'Paid at', 'Refunded (RM)'])
        ->and($rows)->toHaveCount(7) // the header + 6: cancelled included, the unpaid hold and Thursday not
        ->and($rows[1])->toBe([$week['cash']->code, '2026-11-02', '18:00', '20:00', 'Court 1', 'Badminton', 'Player Chong',
            '+60123456789', 'chong@example.com', 'Online', 'Confirmed', '40.00', 'Yes', 'Cash', '2026-11-02 10:00', ''])
        ->and($rows[2])->toBe([$week['walkIn']->code, '2026-11-02', '19:00', '20:00', 'Court 2', 'Badminton', 'Walk-in Wong',
            '+60111111111', '', 'Walk-in', 'Confirmed', '20.00', 'Yes', 'Card', '2026-11-02 10:00', ''])
        ->and($rows[3][15])->toBe('5.00') // the online one's refund
        ->and($rows[4][10])->toBe('Cancelled')
        ->and($rows[5][10])->toBe('A no-show');
});

it('stops spreadsheet formulas hidden in names (CSV injection), but leaves phone numbers alone', function () {
    [$owner, , , $venue] = reportVenue();
    $player = User::factory()->create(['name' => '=HYPERLINK("http://evil.example","Click")', 'phone' => '+60123456789']);
    reportBooking($venue, 'Court 1', '2026-11-02T18:00', 60, $player);

    $csv = $this->actingAs($owner)->get(reportUrl($venue, path: '/bookings.csv'))->streamedContent();
    $row = str_getcsv(explode("\n", trim(substr($csv, 3)))[1]);

    expect($row[6])->toBe('\'=HYPERLINK("http://evil.example","Click")')
        ->and($row[7])->toBe('+60123456789');
});

it('FR-33 gives the admin platform totals, without the demo venues', function () {
    [$owner, , $player, $venue] = reportVenue();
    reportWeek($venue, $player, $owner); // 6 booked this month (Thursday too)
    Venue::factory()->create(); // waiting for approval
    Venue::factory()->suspended()->create();
    $demo = Venue::factory()->active()->create();
    $demo->forceFill(['is_demo' => true])->save();
    $admin = User::factory()->withTwoFactor()->create()->forceFill(['is_platform_admin' => true]);

    $this->actingAs($admin)->getJson('/api/v1/admin/stats')
        ->assertOk()
        ->assertJsonPath('data.venues', ['active' => 1, 'pending' => 1, 'suspended' => 1])
        ->assertJsonPath('data.accounts', User::count())
        ->assertJsonPath('data.bookings', ['this_month' => 6, 'all_time' => 6])
        ->assertJsonPath('data.booked_sen', ['this_month' => 14000, 'all_time' => 14000]);

    $this->actingAs($owner)->getJson('/api/v1/admin/stats')->assertForbidden();
});
