<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Exceptions\ApiException;
use App\Models\Booking;

/**
 * The jobs that run every minute (ADR-022):
 *   - expire unpaid holds whose time is up, freeing their half hours (FR-16);
 *   - mark checked-in bookings that have ended as completed.
 *
 * Run by `php artisan courtku:tick` (the scheduler, on a server that has one) and by
 * POST /api/tick (a free cron service, on hosting without a scheduler, like Render's free plan).
 * Running it twice, or late, is harmless: each booking changes at most once.
 */
class Housekeeping
{
    public function __construct(
        private readonly Holds $holds = new Holds,
        private readonly BookingStateMachine $machine = new BookingStateMachine,
    ) {}

    /**
     * @return array{expired_holds: int, completed_bookings: int}
     */
    public function run(): array
    {
        return [
            'expired_holds' => $this->holds->releaseExpired(),
            'completed_bookings' => $this->completeFinished(),
        ];
    }

    /** checked_in -> completed once the booking's time is over. Their half hours stay taken, as history. */
    private function completeFinished(): int
    {
        $finished = Booking::where('status', BookingStatus::CheckedIn->value)
            ->where('ends_at', '<=', now())
            ->get();

        $completed = 0;
        foreach ($finished as $booking) {
            try {
                $this->machine->transition($booking, BookingStatus::Completed, null);
                $completed++;
            } catch (ApiException) {
                // Changed by someone else a moment ago.
            }
        }

        return $completed;
    }
}
