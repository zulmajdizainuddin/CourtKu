<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Exceptions\ApiException;
use App\Models\Booking;
use App\Models\WebhookEvent;

/**
 * The jobs that run every minute (ADR-022):
 *   - expire unpaid holds whose time is up, freeing their half hours (FR-16);
 *   - mark checked-in bookings that have ended as completed;
 *   - finish payment webhooks that were saved but failed half way (PaymentProcessor);
 *   - look up payments still pending after 20 minutes with the gateway, each every 15 (reconciliation).
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
        private readonly PaymentProcessor $payments = new PaymentProcessor,
    ) {}

    /**
     * @return array{expired_holds: int, completed_bookings: int, retried_webhooks: int, reconciled_payments: int}
     */
    public function run(): array
    {
        // Payments first: a hold paid just in time is confirmed before the expiry looks at it.
        $retried = $this->retryWebhooks();
        $reconciled = app(Checkouts::class)->reconcile();

        return [
            'expired_holds' => $this->holds->releaseExpired(),
            'completed_bookings' => $this->completeFinished(),
            'retried_webhooks' => $retried,
            'reconciled_payments' => $reconciled,
        ];
    }

    /** Webhooks saved more than a minute ago but never acted on (the request failed half way). */
    private function retryWebhooks(): int
    {
        $waiting = WebhookEvent::whereNull('processed_at')->where('created_at', '<=', now()->subMinute())->get();
        foreach ($waiting as $event) {
            $this->payments->process($event);
        }

        return $waiting->count();
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
