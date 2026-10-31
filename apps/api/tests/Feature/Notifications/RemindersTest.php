<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Notifications\BookingReminder;
use App\Services\BookingService;
use App\Services\BookingStateMachine;
use App\Services\Housekeeping;
use App\Services\Reminders;
use Carbon\CarbonImmutable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;

/*
| FR-26: "your game is tomorrow", a day before the start.
| Now is Monday 2 Nov 2026, 10:00 in Malaysia (02:00 UTC). The venue is open 08:00–23:00 every day.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
    Notification::fake();
});

/** @return array{User, Venue} a player and the venue */
function reminderVenue(array $settings = []): array
{
    $owner = User::factory()->withTwoFactor()->create();
    $player = User::factory()->create(['name' => 'Player Chong']);
    $venue = Venue::factory()->active()->ownedBy($owner)->create([
        'name' => 'Arena Remind', 'address_line' => 'Lot 12, Jalan Industri 3', 'postcode' => '47000', 'city' => 'Sungai Buloh',
        'phone' => '+60312345678', ...$settings,
    ]);
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'base_price_sen' => 2000]);

    return [$player, $venue];
}

/** A booking made now through the real engine, e.g. for Wednesday 6 pm. */
function reminderBooking(Venue $venue, ?User $player, string $startsAt = '2026-11-04T18:00:00+08:00', bool $hold = false): Booking
{
    $service = new BookingService;

    return $service->store(
        $service->prepare($venue->courts()->firstOrFail(), CarbonImmutable::parse($startsAt), 60),
        $player ? BookingSource::Online : BookingSource::WalkIn,
        player: $player,
        customerName: $player ? null : 'Walk-in Wong',
        customerPhone: $player ? null : '+60111111111',
        hold: $hold,
    );
}

/** Malaysia time "2026-11-03 17:59" -> travel there. */
function reminderAt(string $malaysiaTime): void
{
    test()->travelTo(CarbonImmutable::parse($malaysiaTime, 'Asia/Kuala_Lumpur')->utc());
}

function reminderText(MailMessage $mail): string
{
    return implode("\n", [$mail->subject, $mail->greeting, ...$mail->introLines, $mail->actionText, ...$mail->outroLines]);
}

it('FR-26 reminds the player exactly a day before, and only once', function () {
    [$player, $venue] = reminderVenue();
    $booking = reminderBooking($venue, $player); // Wednesday 6 pm, booked on Monday

    reminderAt('2026-11-03 17:59');
    expect((new Reminders)->queueDue())->toBe(0);

    reminderAt('2026-11-03 18:00');
    expect((new Reminders)->queueDue())->toBe(1)
        ->and($booking->fresh()->reminder_sent_at)->not->toBeNull();

    reminderAt('2026-11-03 18:01');
    expect((new Reminders)->queueDue())->toBe(0);

    Notification::assertSentToTimes($player, BookingReminder::class, 1);
});

it('FR-26 sends no reminder for a booking made less than a day ahead: the "Booked" email just went out', function () {
    [$player, $venue] = reminderVenue();
    reminderBooking($venue, $player, '2026-11-03T09:00:00+08:00'); // tomorrow 9 am, 23 hours away

    reminderAt('2026-11-02 12:00');
    expect((new Reminders)->queueDue())->toBe(0);
    Notification::assertNotSentTo($player, BookingReminder::class);
});

it('sends no reminder for walk-ins, unpaid holds or cancelled bookings', function () {
    [$player, $venue] = reminderVenue();
    reminderBooking($venue, null, '2026-11-04T08:00:00+08:00');
    reminderBooking($venue, $player, '2026-11-04T10:00:00+08:00', hold: true);
    $cancelled = reminderBooking($venue, $player, '2026-11-04T12:00:00+08:00');
    (new BookingStateMachine)->transition($cancelled, BookingStatus::Cancelled, $player, 'Cancelled by the player');

    reminderAt('2026-11-03 20:00');
    expect((new Reminders)->queueDue())->toBe(0);
    Notification::assertNotSentTo($player, BookingReminder::class);
});

it('still reminds the player when the tick was down, but never after the game started', function () {
    [$player, $venue] = reminderVenue();
    $late = reminderBooking($venue, $player, '2026-11-04T18:00:00+08:00');
    $missed = reminderBooking($venue, $player, '2026-11-04T16:00:00+08:00');

    reminderAt('2026-11-04 17:00'); // the tick comes back an hour before the 6 pm game, after the 4 pm one began
    expect((new Reminders)->queueDue())->toBe(1)
        ->and($late->fresh()->reminder_sent_at)->not->toBeNull()
        ->and($missed->fresh()->reminder_sent_at)->toBeNull();
});

it('FR-24 tells the player to call the venue once the online cancel time has passed', function () {
    [$player, $venue] = reminderVenue(); // cancel online until 24 hours before
    reminderBooking($venue, $player);

    reminderAt('2026-11-03 18:00');
    (new Reminders)->queueDue();

    Notification::assertSentTo($player, BookingReminder::class, function (BookingReminder $email) use ($player) {
        $text = reminderText($email->toMail($player));

        return str_contains($text, 'Reminder: Court 1 at Arena Remind, Wed 4 Nov, 6:00 pm')
            && str_contains($text, "Don't forget your game at Arena Remind, Wednesday at 6:00 pm.")
            && str_contains($text, '**When:** Wed 4 Nov 2026, 6:00 pm to 7:00 pm')
            && str_contains($text, "Can't make it? Please call the venue on +60312345678")
            && str_contains($text, 'google.com/maps/search/?api=1&query=Arena%20Remind%2C%20Lot%2012');
    });
});

it('FR-24 says until when the player can still cancel online, when that is later than the reminder', function () {
    [$player, $venue] = reminderVenue(['cancel_cutoff_hours' => 2]);
    reminderBooking($venue, $player);

    reminderAt('2026-11-03 18:00');
    (new Reminders)->queueDue();

    Notification::assertSentTo($player, BookingReminder::class,
        fn (BookingReminder $email) => str_contains(reminderText($email->toMail($player)), "Can't make it? Please cancel online before Wed 4 Nov, 4:00 pm"));
});

it('skips a queued reminder for a booking cancelled before it went out', function () {
    [$player, $venue] = reminderVenue();
    $booking = reminderBooking($venue, $player);
    $reminder = new BookingReminder($booking);

    (new BookingStateMachine)->transition($booking, BookingStatus::Cancelled, $player, 'Cancelled by the player');

    expect($reminder->shouldSend($player, 'mail'))->toBeTrue() // the copy queued earlier still says confirmed...
        ->and((new BookingReminder($booking->fresh()))->shouldSend($player, 'mail'))->toBeFalse(); // ...but the queue reads it again when sending
});

it('runs from the minute tick', function () {
    [$player, $venue] = reminderVenue();
    reminderBooking($venue, $player);

    reminderAt('2026-11-03 18:00');
    expect(app(Housekeeping::class)->run()['reminders'])->toBe(1);
});
