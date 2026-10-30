<?php

namespace App\Enums;

/**
 * Where a booking is in its life. Only BookingStateMachine changes it.
 *
 * In M3 every booking is paid at the venue, so it starts as confirmed. "Pending payment" and
 * "expired" (checkout holds) arrive with online payment in M4 (ADR-021).
 */
enum BookingStatus: string
{
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case Completed = 'completed';
    case NoShow = 'no_show';
    case Cancelled = 'cancelled';

    /**
     * The statuses this one may change to. Anything else is a mistake, e.g. a cancelled booking
     * can't be checked in.
     *
     * @return list<self>
     */
    public function next(): array
    {
        return match ($this) {
            self::Confirmed => [self::CheckedIn, self::NoShow, self::Cancelled],
            self::CheckedIn => [self::Completed],
            self::Completed, self::NoShow, self::Cancelled => [],
        };
    }

    /** For messages: "This booking is cancelled, so it can't be marked checked in." */
    public function label(): string
    {
        return match ($this) {
            self::Confirmed => 'confirmed',
            self::CheckedIn => 'checked in',
            self::Completed => 'completed',
            self::NoShow => 'a no-show',
            self::Cancelled => 'cancelled',
        };
    }

    /** Cancelled bookings give their half hours back; the others keep them, as history. */
    public function holdsSlots(): bool
    {
        return $this !== self::Cancelled;
    }
}
