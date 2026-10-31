<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentProblem;
use App\Enums\PaymentStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Notifications\PaymentNeedsAttention;
use App\Payments\Checkout;
use App\Payments\CheckoutStatus;
use App\Payments\PaymentEvent;
use App\Payments\PaymentEventType;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Acts on what the gateway says about a payment (plan section 9, steps 7 and 8):
 *   1. save the event once (webhook_events, unique event_key): the same webhook twice changes nothing;
 *   2. find the payment, and check the event is from the venue's own Stripe account;
 *   3. paid: check the amount, then confirm the booking, all under row locks in one transaction.
 *
 * Render's free plan has no background worker (ADR-022), so this runs inside the webhook request.
 * It is a few quick queries, well within Stripe's time limit. If it fails half way, the event stays
 * saved but unprocessed, and the every-minute tick finishes it (Housekeeping).
 */
class PaymentProcessor
{
    public function __construct(private readonly BookingStateMachine $machine = new BookingStateMachine) {}

    /**
     * Saves a webhook's event; returns null when this exact event was already acted on.
     */
    public function save(string $gateway, PaymentEvent $event): ?WebhookEvent
    {
        $saved = WebhookEvent::where('event_key', $event->id)->first();
        if ($saved === null) {
            $saved = new WebhookEvent;
            $saved->forceFill([
                'gateway' => $gateway,
                'event_key' => $event->id,
                'type' => $event->type->value,
                'account_id' => $event->accountId,
                // What CourtKu read from the signed webhook; enough to act on it again later.
                'payload' => [
                    'checkout_id' => $event->checkout->id,
                    'paid' => $event->checkout->paid,
                    'amount_sen' => $event->checkout->amountSen,
                    'currency' => $event->checkout->currency,
                    'booking_code' => $event->checkout->bookingCode,
                ],
            ]);
            try {
                $saved->save();
            } catch (UniqueConstraintViolationException) {
                // The same webhook arrived twice at the same moment: the other request has it.
                return null;
            }
        }

        return $saved->processed_at === null ? $saved : null;
    }

    /** Acts on a saved event, once. */
    public function process(WebhookEvent $event): void
    {
        DB::transaction(function () use ($event) {
            $locked = WebhookEvent::whereKey($event->id)->lockForUpdate()->firstOrFail();
            if ($locked->processed_at !== null) {
                return; // done by another request a moment ago
            }

            $payment = Payment::where('checkout_id', $locked->payload['checkout_id'] ?? '')->lockForUpdate()->first();
            if ($payment === null) {
                Log::warning('Payment webhook for a payment page CourtKu doesn\'t know', ['event' => $locked->event_key]);
            } elseif ($locked->account_id !== null && $locked->account_id !== $payment->account_id) {
                // Signed by Stripe, but about another venue's account: never let it touch this payment.
                Log::warning('Payment webhook from the wrong Stripe account', ['event' => $locked->event_key, 'payment' => $payment->id]);
            } else {
                match (PaymentEventType::from($locked->type)) {
                    PaymentEventType::Paid => $this->paid($payment, (int) ($locked->payload['amount_sen'] ?? 0), (string) ($locked->payload['currency'] ?? '')),
                    PaymentEventType::Failed => $this->endWithout($payment, PaymentStatus::Failed),
                    PaymentEventType::Expired => $this->endWithout($payment, PaymentStatus::Expired),
                };
            }

            $locked->forceFill(['processed_at' => now()])->save();
        });
    }

    /**
     * Acts on what CourtKu learned by asking the gateway itself (the player came back, or the
     * reconciliation): the same rules as a webhook, under the same row lock.
     */
    public function applyCheckout(Payment $payment, Checkout $checkout): void
    {
        DB::transaction(function () use ($payment, $checkout) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($checkout->paid) {
                $this->paid($locked, $checkout->amountSen, $checkout->currency);
            } elseif ($checkout->status === CheckoutStatus::Expired) {
                $this->endWithout($locked, PaymentStatus::Expired);
            }
        });
    }

    /**
     * The money came in. The payment row is already locked by the caller. Confirm the booking only when
     * the amount is right and the booking is still waiting for this payment; otherwise flag a problem
     * for the owner, and the booking stays as it is.
     */
    private function paid(Payment $payment, int $paidSen, string $currency): void
    {
        if ($payment->status === PaymentStatus::Paid) {
            return; // already done: a repeated or late copy of the news changes nothing
        }

        $payment->forceFill(['status' => PaymentStatus::Paid, 'paid_amount_sen' => $paidSen, 'paid_at' => now()]);
        $booking = Booking::whereKey($payment->booking_id)->lockForUpdate()->firstOrFail();

        $problem = match (true) {
            // Plan section 9: confirm only when what was paid equals what the booking costs.
            $paidSen !== $payment->amount_sen || strtolower($currency) !== $payment->currency => PaymentProblem::AmountMismatch,
            $booking->status === BookingStatus::PendingPayment => null,
            // Paid after the hold ran out: take the time back if it is still free, otherwise refund (plan section 7).
            $booking->status === BookingStatus::Expired => $this->takeTimeBack($booking) ? null : PaymentProblem::BookingExpired,
            $booking->status === BookingStatus::Cancelled => PaymentProblem::BookingCancelled,
            default => PaymentProblem::AlreadyPaid, // e.g. paid twice: the booking was confirmed by an earlier payment
        };

        if ($problem === null) {
            if ($booking->status === BookingStatus::PendingPayment) {
                $this->machine->transition($booking, BookingStatus::Confirmed, null, 'Paid online');
            } else {
                $this->machine->reclaim($booking, 'Paid online after the hold ran out; the time was still free');
            }
            $booking->refresh()->forceFill(['paid_at' => now(), 'payment_method' => PaymentMethod::Online])->save();
            AuditLog::record($booking, 'booking.paid', null, ['method' => 'online', 'total_sen' => $paidSen, 'payment_id' => $payment->id]);
        } else {
            $payment->problem = $problem;
            AuditLog::record($booking, 'payment.problem', null, [
                'problem' => $problem->value,
                'payment_id' => $payment->id,
                'paid_sen' => $paidSen,
                'expected_sen' => $payment->amount_sen,
            ]);
            Log::warning('Online payment needs the owner\'s attention', ['payment' => $payment->id, 'problem' => $problem->value]);
            // Tell the owner and the player once the change is saved; an email that fails never undoes it.
            DB::afterCommit(fn () => $this->tellAboutProblem($payment, $booking));
        }

        $payment->save();
    }

    /**
     * Gives an expired booking its half hours back, if nobody took them meanwhile and the booking hasn't
     * started yet. The rows go in inside a savepoint: if any half hour is taken, the unique index refuses it
     * (ADR-005) and nothing is kept. The prices per half hour aren't known any more (they went with the
     * hold); the booking's total still is.
     */
    private function takeTimeBack(Booking $booking): bool
    {
        if (! $booking->starts_at->isFuture()) {
            return false;
        }

        try {
            DB::transaction(function () use ($booking) {
                $now = now();
                $cells = [];
                for ($start = $booking->starts_at->copy(); $start->lessThan($booking->ends_at); $start->addMinutes(PricingService::CELL_MINUTES)) {
                    $cells[] = [
                        'court_id' => $booking->court_id,
                        'slot_start' => $start->format('Y-m-d H:i:s'),
                        'booking_id' => $booking->id,
                        'price_sen' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                BookingSlot::insert($cells);
            });
        } catch (UniqueConstraintViolationException) {
            return false;
        }

        return true;
    }

    /** Emails the venue's owners (to sort it out, usually a refund) and the player (FR-25, plan section 9). */
    private function tellAboutProblem(Payment $payment, Booking $booking): void
    {
        try {
            $booking->loadMissing(['venue', 'court', 'user']);
            Notification::send($booking->venue->owners, new PaymentNeedsAttention($payment, $booking, forOwner: true));
            $booking->user?->notify(new PaymentNeedsAttention($payment, $booking, forOwner: false));
        } catch (Throwable $e) {
            Log::error('Could not email about a payment problem', ['payment' => $payment->id, 'error' => $e->getMessage()]);
        }
    }

    /** The payment page ended without money (failed or closed). A paid payment never goes back. */
    private function endWithout(Payment $payment, PaymentStatus $status): void
    {
        if ($payment->status === PaymentStatus::Pending) {
            $payment->forceFill(['status' => $status])->save();
        }
    }
}
