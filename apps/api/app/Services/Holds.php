<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Exceptions\ApiException;
use App\Models\Booking;
use App\Models\User;
use App\Models\Venue;

/**
 * Frees the time of bookings that weren't paid within the venue's hold time (FR-16).
 *
 * Two things call this:
 *   - every read or booking that needs to know which half hours are free ("lazy" release),
 *     so a player never sees a hold whose time is up, even when no timer has run yet;
 *   - the every-minute tick (courtku:tick), which tidies up the rest.
 *
 * Each hold goes through BookingStateMachine, which locks the booking first. So when a payment
 * confirms a hold at the same moment, only one of the two wins: the other sees the new status
 * and leaves it alone.
 */
class Holds
{
    public function __construct(private readonly BookingStateMachine $machine = new BookingStateMachine) {}

    /**
     * Expires every hold whose time is up, at one venue or for one player, or everywhere when
     * both are null. Returns how many it expired.
     */
    public function releaseExpired(?Venue $venue = null, ?User $player = null): int
    {
        $holds = Booking::where('status', BookingStatus::PendingPayment->value)
            ->where('hold_expires_at', '<=', now())
            ->when($venue !== null, fn ($query) => $query->where('venue_id', $venue?->id))
            ->when($player !== null, fn ($query) => $query->where('user_id', $player?->id))
            ->get();

        $expired = 0;
        foreach ($holds as $hold) {
            try {
                $this->machine->transition($hold, BookingStatus::Expired, null, 'Not paid in time');
                $expired++;
            } catch (ApiException) {
                // Paid, cancelled or expired by someone else a moment ago: nothing left to do.
            }
        }

        return $expired;
    }
}
