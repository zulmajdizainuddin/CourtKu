<?php

namespace App\Enums;

/**
 * Where a booking is in its life. Only BookingStateMachine changes it.
 *
 * A booking paid online starts as pending_payment: its half hours are held for the venue's hold
 * time while the player pays (FR-16). Paid in time, it becomes confirmed; not paid in time, it
 * becomes expired and the half hours are free again. A booking paid at the venue starts as
 * confirmed (ADR-021).
 */
enum BookingStatus: string
{
    case PendingPayment = 'pending_payment';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case Completed = 'completed';
    case NoShow = 'no_show';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    /**
     * The statuses this one may change to. Anything else is a mistake, e.g. a cancelled booking
     * can't be checked in, and an expired one can't simply become confirmed.
     *
     * @return list<self>
     */
    public function next(): array
    {
        return match ($this) {
            self::PendingPayment => [self::Confirmed, self::Expired, self::Cancelled],
            self::Confirmed => [self::CheckedIn, self::NoShow, self::Cancelled],
            self::CheckedIn => [self::Completed],
            self::Completed, self::NoShow, self::Cancelled, self::Expired => [],
        };
    }

    /** For messages: "This booking is cancelled, so it can't be marked checked in." */
    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'waiting for payment',
            self::Confirmed => 'confirmed',
            self::CheckedIn => 'checked in',
            self::Completed => 'completed',
            self::NoShow => 'a no-show',
            self::Cancelled => 'cancelled',
            self::Expired => 'expired',
        };
    }

    /** Cancelled and expired bookings give their half hours back; the others keep them (a hold, or history). */
    public function holdsSlots(): bool
    {
        return ! in_array($this, [self::Cancelled, self::Expired], true);
    }

    /**
     * Bookings that still take up their time: what the staff schedule shows and what blocks
     * changes to opening hours (FR-38).
     *
     * @return list<string>
     */
    public static function activeValues(): array
    {
        return [self::PendingPayment->value, self::Confirmed->value, self::CheckedIn->value];
    }
}
