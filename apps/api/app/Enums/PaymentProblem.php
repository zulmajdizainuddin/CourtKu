<?php

namespace App\Enums;

/**
 * Money that came in but couldn't simply confirm the booking (plan section 9, failure cases). The booking
 * isn't confirmed by it, and the owner sorts it out, usually with a refund (FR-25).
 */
enum PaymentProblem: string
{
    case AmountMismatch = 'amount_mismatch'; // paid a different amount than the booking costs
    case BookingExpired = 'booking_expired'; // paid after the hold ran out and the time was taken meanwhile (if free, it is taken back)
    case BookingCancelled = 'booking_cancelled'; // paid for a booking that was cancelled meanwhile
    case AlreadyPaid = 'already_paid'; // paid twice: an earlier payment had already confirmed the booking

    /** What happened, in one sentence for the owner and the player. */
    public function describe(): string
    {
        return match ($this) {
            self::AmountMismatch => 'The amount paid didn\'t match the booking\'s total, so the booking wasn\'t confirmed.',
            self::BookingExpired => 'The payment arrived after the time to pay ran out, and someone else had taken the court meanwhile.',
            self::BookingCancelled => 'The payment arrived after the booking was cancelled.',
            self::AlreadyPaid => 'The booking was paid twice.',
        };
    }
}
