<?php

namespace App\Services;

use App\Demo\DemoWorld;
use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingConfirmed;
use App\Notifications\NewPlayerBooking;
use App\Notifications\PlayerCancelledBooking;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Who gets an email when a booking is confirmed or cancelled (FR-26, FR-27):
 *
 *   confirmed (paid online, or booked to pay at the venue)
 *     -> the player: "Booked"
 *     -> the venue's owners and staff, for bookings players made on the website: "New booking"
 *   cancelled, after it was confirmed
 *     -> the player: "Cancelled" (by them, or by the venue with its reason)
 *     -> the venue's owners and staff, when the player cancelled: "Cancelled by the player"
 *
 * Walk-in and phone bookings have no player account, so nobody is emailed about them; an unpaid
 * hold that is given up or runs out was never booked, so neither is. The venue's owner can switch
 * the venue emails off (venues.booking_emails). The demo world's made-up players are never emailed
 * (ADR-030): their bookings would only waste the daily email allowance.
 *
 * The emails are queued once the change is saved and sent by the minute tick (ADR-027), so a slow
 * or failing email server never slows down, or undoes, a booking.
 */
class BookingEmails
{
    public function confirmed(Booking $booking): void
    {
        if ($this->isDemo($booking)) {
            return;
        }

        $this->afterSaving($booking, function (Booking $booking) {
            $booking->user?->notify(new BookingConfirmed($booking));

            if ($booking->source === BookingSource::Online) {
                $this->tellVenue($booking, new NewPlayerBooking($booking));
            }
        });
    }

    public function cancelled(Booking $booking, BookingStatus $from, ?User $by): void
    {
        if ($from !== BookingStatus::Confirmed || $this->isDemo($booking)) {
            return;
        }

        $byPlayer = $by !== null && $by->id === $booking->user_id;

        $this->afterSaving($booking, function (Booking $booking) use ($byPlayer) {
            $booking->user?->notify(new BookingCancelled($booking, $byPlayer));

            if ($byPlayer) {
                $this->tellVenue($booking, new PlayerCancelledBooking($booking));
            }
        });
    }

    /** Made by the demo world (ADR-030): a demo player, or a walk-in at a demo venue. */
    private function isDemo(Booking $booking): bool
    {
        return $booking->user_id === null
            ? (bool) $booking->venue?->is_demo
            : DemoWorld::isDemoPlayer($booking->user);
    }

    private function tellVenue(Booking $booking, NewPlayerBooking|PlayerCancelledBooking $email): void
    {
        if ($booking->venue->booking_emails) {
            Notification::send($booking->venue->members, $email);
        }
    }

    /**
     * Runs once the whole change is saved (nothing is sent for a change that is rolled back), with the
     * booking read again, so the email shows what was saved last, e.g. "paid online". A failure is
     * logged and never undoes the booking.
     *
     * @param  Closure(Booking): void  $send
     */
    private function afterSaving(Booking $booking, Closure $send): void
    {
        DB::afterCommit(function () use ($booking, $send) {
            try {
                $send($booking->fresh(['venue.members', 'court', 'sport', 'user']) ?? $booking);
            } catch (Throwable $e) {
                Log::error('Could not queue a booking email', ['booking' => $booking->id, 'error' => $e->getMessage()]);
            }
        });
    }
}
