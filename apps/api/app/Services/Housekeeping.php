<?php

namespace App\Services;

use App\Demo\DemoWorld;
use App\Enums\BookingStatus;
use App\Exceptions\ApiException;
use App\Models\Booking;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Throwable;

/**
 * The jobs that run every minute (ADR-022):
 *   - expire unpaid holds whose time is up, freeing their half hours (FR-16);
 *   - mark checked-in bookings that have ended as completed;
 *   - finish payment webhooks that were saved but failed half way (PaymentProcessor);
 *   - look up payments still pending after 20 minutes with the gateway, each every 15 (reconciliation);
 *   - keep the demo world up to date, a few seconds at a time (ADR-030, DemoWorld);
 *   - queue the "your game is tomorrow" reminders that are due (FR-26, Reminders);
 *   - send the emails waiting in the queue (ADR-027).
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
        private readonly Reminders $reminders = new Reminders,
        private readonly DemoWorld $demo = new DemoWorld,
    ) {}

    /**
     * @return array{expired_holds: int, completed_bookings: int, retried_webhooks: int, reconciled_payments: int, demo_bookings: int, reminders: int, emails_sent: int}
     */
    public function run(): array
    {
        // Payments first: a hold paid just in time is confirmed before the expiry looks at it.
        $retried = $this->retryWebhooks();
        $reconciled = app(Checkouts::class)->reconcile();
        $expired = $this->holds->releaseExpired();
        $completed = $this->completeFinished();
        $demo = $this->demoStep();
        $reminders = $this->reminders->queueDue();

        return [
            'expired_holds' => $expired,
            'completed_bookings' => $completed,
            'retried_webhooks' => $retried,
            'reconciled_payments' => $reconciled,
            'demo_bookings' => $demo['bookings'],
            'reminders' => $reminders,
            // Last, so emails queued by the jobs above go out in this same tick.
            'emails_sent' => $this->sendQueuedEmails(),
        ];
    }

    /**
     * Works through the queue (ADR-027): at most 50 jobs and 20 seconds, so the cron call stays short;
     * anything left goes out next minute. A failed email is tried again later (see DescribesBooking).
     * With QUEUE_CONNECTION=sync there is never anything waiting.
     */
    private function sendQueuedEmails(): int
    {
        $waiting = Queue::size();
        if ($waiting === 0) {
            return 0;
        }

        Artisan::call('queue:work', ['--stop-when-empty' => true, '--max-jobs' => 50, '--max-time' => 20, '--sleep' => 0]);

        return max(0, $waiting - Queue::size());
    }

    /**
     * The demo world's few seconds (ADR-030). It must never stop the real jobs around it: a failure is
     * reported to Sentry (docs/deploy.md) and the tick carries on.
     *
     * @return array{bookings: int}
     */
    private function demoStep(): array
    {
        try {
            return $this->demo->step();
        } catch (Throwable $e) {
            report($e);

            return ['bookings' => 0];
        }
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
