<?php

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingConfirmed;
use App\Notifications\NewPlayerBooking;
use App\Notifications\PlayerCancelledBooking;
use App\Services\BookingService;
use App\Services\BookingStateMachine;
use App\Services\Holds;
use App\Services\Housekeeping;
use Carbon\CarbonImmutable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/*
| FR-26 (player emails) and FR-27 (venue emails), queued and sent by the tick (ADR-027).
| Now is Monday 2 Nov 2026, 10:00 in Malaysia (02:00 UTC). The venue is open 08:00–23:00 every day
| and players can cancel online until 24 hours before.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
});

/** @return array{User, User, User, Venue} the owner, a staff member, a player and the venue */
function mailVenue(array $settings = []): array
{
    $owner = User::factory()->withTwoFactor()->create(['name' => 'Owner Aina']);
    $staff = User::factory()->create(['name' => 'Staff Ben']);
    $player = User::factory()->create(['name' => 'Player Chong', 'email' => 'chong@example.com', 'phone' => '+60123456789']);
    $venue = Venue::factory()->active()->ownedBy($owner)->create([
        'name' => 'Arena Mail',
        'slug' => 'arena-mail',
        'address_line' => 'Lot 12, Jalan Industri 3',
        'postcode' => '47000',
        'city' => 'Sungai Buloh',
        'phone' => '+60312345678',
        ...$settings,
    ]);
    $venue->members()->attach($staff, ['role' => 'staff']);
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'base_price_sen' => 2000]);

    return [$owner, $staff, $player, $venue];
}

/** A booking through the real engine: Wednesday 6 pm, 2 hours, RM 40. */
function mailBooking(Venue $venue, ?User $player, bool $hold = false, BookingSource $source = BookingSource::Online): Booking
{
    $service = new BookingService;

    return $service->store(
        $service->prepare($venue->courts()->firstOrFail(), CarbonImmutable::parse('2026-11-04T18:00:00+08:00'), 120),
        $source,
        player: $player,
        createdBy: $player,
        sportId: $venue->courts()->firstOrFail()->sports()->value('sports.id'),
        customerName: $player ? null : 'Walk-in Wong',
        customerPhone: $player ? null : '+60111111111',
        hold: $hold,
    );
}

/** Everything an email says, subject included, as one text. */
function mailText(MailMessage $mail): string
{
    return implode("\n", [$mail->subject, $mail->greeting, ...$mail->introLines, $mail->actionText, $mail->actionUrl, ...$mail->outroLines]);
}

it('FR-26 FR-27 a booking to pay at the venue emails the player, the owner and the staff', function () {
    Notification::fake();
    [$owner, $staff, $player, $venue] = mailVenue();

    $booking = mailBooking($venue, $player);

    Notification::assertSentTo($player, BookingConfirmed::class, function (BookingConfirmed $email) use ($player, $booking) {
        $text = mailText($email->toMail($player));

        return str_contains($text, 'Booked: Court 1 at Arena Mail, Wed 4 Nov')
            && str_contains($text, "**Booking code:** {$booking->code}")
            && str_contains($text, '**Venue:** Arena Mail, Lot 12, Jalan Industri 3, 47000 Sungai Buloh')
            && str_contains($text, '**Court:** Court 1 (Badminton)')
            && str_contains($text, '**When:** Wed 4 Nov 2026, 6:00 pm to 8:00 pm')
            && str_contains($text, '**Price:** RM 40.00, to pay at the venue')
            && str_contains($text, 'You can cancel online until Tue 3 Nov, 6:00 pm. After that, please call the venue on +60312345678.')
            && str_contains($text, "/bookings/{$booking->code}");
    });
    foreach ([$owner, $staff] as $member) {
        Notification::assertSentTo($member, NewPlayerBooking::class, function (NewPlayerBooking $email) use ($member, $venue) {
            $text = mailText($email->toMail($member));

            return str_contains($text, 'Player Chong booked a court at Arena Mail.')
                && str_contains($text, '**Player:** Player Chong, chong@example.com, +60123456789')
                && str_contains($text, "/dashboard/{$venue->id}/schedule?date=2026-11-04");
        });
    }
});

it('FR-26 a hold sends nothing until it is paid, then says "paid online"', function () {
    Notification::fake();
    [$owner, , $player, $venue] = mailVenue();

    $booking = mailBooking($venue, $player, hold: true);
    Notification::assertNothingSent();

    // What PaymentProcessor does when the money comes in: confirm, then record the payment, in one transaction.
    DB::transaction(function () use ($booking) {
        (new BookingStateMachine)->transition($booking, BookingStatus::Confirmed, null, 'Paid online');
        $booking->refresh()->forceFill(['paid_at' => now(), 'payment_method' => PaymentMethod::Online])->save();
    });

    // The email is built after the whole change is saved, so it knows the booking was paid.
    Notification::assertSentTo($player, BookingConfirmed::class,
        fn (BookingConfirmed $email) => str_contains(mailText($email->toMail($player)), '**Price:** RM 40.00, paid online'));
    Notification::assertSentTo($owner, NewPlayerBooking::class);
});

it('emails nobody about an unpaid hold that runs out or is given up', function () {
    Notification::fake();
    [, , $player, $venue] = mailVenue();

    $expires = mailBooking($venue, $player, hold: true);
    $this->travel(20)->minutes();
    (new Holds)->releaseExpired();
    expect($expires->fresh()->status)->toBe(BookingStatus::Expired);

    $givenUp = mailBooking($venue, $player, hold: true);
    $this->actingAs($player)->postJson("/api/v1/bookings/{$givenUp->code}/cancel")->assertOk();

    Notification::assertNothingSent();
});

it('emails nobody about walk-in and phone bookings: staff typed those in themselves', function () {
    Notification::fake();
    [, , , $venue] = mailVenue();

    mailBooking($venue, null, source: BookingSource::WalkIn);

    Notification::assertNothingSent();
});

it('FR-26 FR-27 a player who cancels gets an email, and so do the owner and staff', function () {
    [$owner, $staff, $player, $venue] = mailVenue();
    $booking = mailBooking($venue, $player);
    Notification::fake();

    $this->actingAs($player)->postJson("/api/v1/bookings/{$booking->code}/cancel")->assertOk();

    Notification::assertSentTo($player, BookingCancelled::class, function (BookingCancelled $email) use ($player) {
        $text = mailText($email->toMail($player));

        return str_contains($text, 'Cancelled: Court 1 at Arena Mail, Wed 4 Nov')
            && str_contains($text, 'You cancelled this booking.')
            && str_contains($text, '/v/arena-mail/book');
    });
    Notification::assertSentTo([$owner, $staff], PlayerCancelledBooking::class, function (PlayerCancelledBooking $email) use ($owner) {
        return str_contains(mailText($email->toMail($owner)), 'Player Chong cancelled their booking. The time is free for others to book again.');
    });
});

it('FR-26 a booking the venue cancels tells the player why, and the venue team is not emailed about its own cancel', function () {
    [$owner, $staff, $player, $venue] = mailVenue();
    $booking = mailBooking($venue, $player);
    Notification::fake();

    $this->actingAs($staff)->postJson("/api/v1/manage/venues/{$venue->id}/bookings/{$booking->code}/cancel", ['reason' => 'Roof leak on Court 1'])
        ->assertOk();

    Notification::assertSentTo($player, BookingCancelled::class, function (BookingCancelled $email) use ($player) {
        $text = mailText($email->toMail($player));

        return str_contains($text, 'Arena Mail cancelled your booking.') && str_contains($text, '**Reason:** Roof leak on Court 1');
    });
    Notification::assertNotSentTo([$owner, $staff], PlayerCancelledBooking::class);
});

it('FR-25 a cancelled booking paid online tells the player about the refund and the owner to make it', function () {
    [$owner, , $player, $venue] = mailVenue();
    $booking = mailBooking($venue, $player);
    $booking->forceFill(['paid_at' => now(), 'payment_method' => PaymentMethod::Online])->save();
    Notification::fake();

    $this->actingAs($player)->postJson("/api/v1/bookings/{$booking->code}/cancel")->assertOk();

    Notification::assertSentTo($player, BookingCancelled::class,
        fn (BookingCancelled $email) => str_contains(mailText($email->toMail($player)), 'You paid RM 40.00 online. The venue will refund you'));
    Notification::assertSentTo($owner, PlayerCancelledBooking::class, function (PlayerCancelledBooking $email) use ($owner, $venue) {
        $text = mailText($email->toMail($owner));

        return str_contains($text, 'Please refund them in your Stripe dashboard') && str_contains($text, "/dashboard/{$venue->id}/payments");
    });
});

it('FR-27 the owner can switch the venue emails off; the player still gets theirs', function () {
    Notification::fake();
    [$owner, $staff, $player, $venue] = mailVenue(['booking_emails' => false]);

    $booking = mailBooking($venue, $player);
    $this->actingAs($player)->postJson("/api/v1/bookings/{$booking->code}/cancel")->assertOk();

    Notification::assertSentTo($player, BookingConfirmed::class);
    Notification::assertSentTo($player, BookingCancelled::class);
    Notification::assertNothingSentTo($owner);
    Notification::assertNothingSentTo($staff);
});

it('FR-27 only the owner switches the venue emails, on the Settings tab', function () {
    [$owner, $staff, , $venue] = mailVenue();
    $settings = [
        'slot_minutes' => 60, 'min_minutes' => 60, 'max_minutes' => 180, 'booking_window_days' => 14,
        'min_notice_minutes' => 0, 'cancel_cutoff_hours' => 24,
    ];

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}")->assertJsonPath('data.booking_emails', true);

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/settings", [...$settings, 'booking_emails' => false])
        ->assertOk()
        ->assertJsonPath('data.booking_emails', false);

    // Leaving it out keeps it as it is.
    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/settings", $settings)
        ->assertJsonPath('data.booking_emails', false);

    $this->actingAs($staff)->putJson("/api/v1/manage/venues/{$venue->id}/settings", [...$settings, 'booking_emails' => true])
        ->assertForbidden();
    expect($venue->fresh()->booking_emails)->toBeFalse();
});

it('sends nothing for a booking that was rolled back', function () {
    Notification::fake();
    [, , $player, $venue] = mailVenue();

    try {
        DB::transaction(function () use ($venue, $player) {
            mailBooking($venue, $player);
            throw new RuntimeException('something later in the same change failed');
        });
    } catch (RuntimeException) {
    }

    Notification::assertNothingSent();
});

it('ADR-027 emails wait in the queue and the minute tick sends them', function () {
    config(['queue.default' => 'database']);
    [, , $player, $venue] = mailVenue();

    mailBooking($venue, $player); // player + owner + staff

    expect(DB::table('jobs')->count())->toBe(3)
        ->and(Mail::getSymfonyTransport()->messages())->toHaveCount(0);

    expect(app(Housekeeping::class)->run()['emails_sent'])->toBe(3)
        ->and(DB::table('jobs')->count())->toBe(0)
        ->and(Mail::getSymfonyTransport()->messages())->toHaveCount(3);
});

it('ADR-027 an email server that is down never breaks a booking; the email is tried again later', function () {
    config(['queue.default' => 'database', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.default' => 'smtp']);
    [, , $player, $venue] = mailVenue(['booking_emails' => false]);

    $booking = mailBooking($venue, $player);
    expect($booking->fresh()->status)->toBe(BookingStatus::Confirmed);

    // The tick doesn't crash; the email goes back in the queue to be tried again in a minute.
    expect(app(Housekeeping::class)->run()['emails_sent'])->toBe(0)
        ->and(DB::table('jobs')->count())->toBe(1)
        ->and(DB::table('failed_jobs')->count())->toBe(0);
});
