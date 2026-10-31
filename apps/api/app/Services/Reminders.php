<?php

namespace App\Services;

use App\Demo\DemoVenues;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Notifications\BookingReminder;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * "Your game is tomorrow" (FR-26): one email to the player, a day before the start.
 *
 * Due when a confirmed booking with a player account starts within the next 24 hours and hasn't had its
 * reminder yet. A booking made less than a day before its start gets none: the player has just had the
 * "Booked" email. Walk-in and phone bookings have no account, so no email address.
 *
 * Run by the minute tick (Housekeeping). If the tick was down for a while, reminders still go out once it
 * runs again, as long as the game hasn't started.
 */
class Reminders
{
    /** How long before the start the reminder goes out. */
    public const HOURS_BEFORE = 24;

    /** At most this many per tick; any more go out next minute. */
    private const BATCH = 100;

    /** Queues the reminders that are due. Returns how many. */
    public function queueDue(): int
    {
        $now = now();
        $due = Booking::query()
            ->where('status', BookingStatus::Confirmed->value)
            ->whereNull('reminder_sent_at')
            ->whereNotNull('user_id')
            // The demo world's made-up players (ADR-030) are never emailed.
            ->whereHas('user', fn ($user) => $user->where('email', 'not like', '%@'.DemoVenues::PLAYER_DOMAIN))
            ->where('starts_at', '>', $now)
            ->where('starts_at', '<=', $now->copy()->addHours(self::HOURS_BEFORE))
            ->whereRaw('created_at <= starts_at - INTERVAL '.self::HOURS_BEFORE.' HOUR')
            ->orderBy('starts_at')
            ->limit(self::BATCH)
            ->get();

        $queued = 0;
        foreach ($due as $booking) {
            // Claim it first: if two ticks run at once, only the one whose update changes the row sends it.
            $claimed = Booking::whereKey($booking->id)->whereNull('reminder_sent_at')->update(['reminder_sent_at' => $now]);
            if ($claimed === 0) {
                continue;
            }

            try {
                $booking->loadMissing(['venue', 'court', 'sport', 'user']);
                $booking->user?->notify(new BookingReminder($booking));
                $queued++;
            } catch (Throwable $e) {
                // Not queued: give it back, so the next tick tries again.
                Booking::whereKey($booking->id)->update(['reminder_sent_at' => null]);
                Log::error('Could not queue a booking reminder', ['booking' => $booking->id, 'error' => $e->getMessage()]);
            }
        }

        return $queued;
    }
}
