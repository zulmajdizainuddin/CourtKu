<?php

namespace App\Enums;

/**
 * Money that came in but couldn't simply confirm the booking (plan section 9, failure cases). The booking
 * isn't confirmed by it, and the owner sorts it out, usually with a refund (FR-25).
 */
enum PaymentProblem: string
{
    case AmountMismatch = 'amount_mismatch'; // paid a different amount than the booking costs
    case BookingExpired = 'booking_expired'; // paid after the hold ran out (late payment; M4 PR 6 re-claims the time if free)
    case BookingCancelled = 'booking_cancelled'; // paid for a booking that was cancelled meanwhile
    case AlreadyPaid = 'already_paid'; // paid twice: an earlier payment had already confirmed the booking
}
