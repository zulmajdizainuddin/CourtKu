<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Exceptions\ApiException;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The only place a booking's status changes (plan section 7, "Booking status lifecycle").
 *
 *   confirmed  -> checked_in, no_show or cancelled
 *   checked_in -> completed
 *
 * The booking is re-read with a row lock (lockForUpdate) inside a transaction first, so two
 * changes at the same moment (e.g. two staff tapping "cancel" and "check in") happen one after
 * the other, and the second sees the first's result. Every change is written to the audit log.
 */
class BookingStateMachine
{
    public function transition(Booking $booking, BookingStatus $to, ?User $by, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($booking, $to, $by, $reason) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $from = $locked->status;

            if (! in_array($to, $from->next(), true)) {
                throw new ApiException(
                    'INVALID_BOOKING_STATUS',
                    "This booking is {$from->label()}, so it can't be marked {$to->label()}.",
                );
            }

            $locked->status = $to;
            match ($to) {
                BookingStatus::Cancelled => $locked->forceFill(['cancelled_at' => now(), 'cancel_reason' => $reason]),
                BookingStatus::CheckedIn => $locked->forceFill(['checked_in_at' => now()]),
                default => null,
            };
            $locked->save();

            // A cancelled booking gives its half hours back, so others can book them.
            if (! $to->holdsSlots()) {
                $locked->slots()->delete();
            }

            AuditLog::record($locked, "booking.{$to->value}", $by, array_filter([
                'from' => $from->value,
                'to' => $to->value,
                'reason' => $reason,
            ]));

            return $locked;
        });
    }
}
