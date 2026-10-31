<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\ApiException;
use App\Models\Booking;
use App\Models\Payment;
use App\Payments\CheckoutRequest;
use App\Payments\PaymentGatewayException;
use App\Payments\PaymentGateways;
use App\Support\MalaysiaTime;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Paying online for a held booking (FR-22, plan section 9, happy path):
 *   start():   opens the gateway's payment page on the venue's own account and remembers it (payments);
 *   refresh(): asks the gateway how the payment stands, when the player comes back from it.
 *
 * Asking the gateway is as trustworthy as its signed webhook: CourtKu asks Stripe itself, with its own
 * key. What the player's browser brings back in the address is never used (ADR-006). On a laptop,
 * which Stripe's webhook can't reach, this is how a payment gets confirmed.
 */
class Checkouts
{
    /** A payment page is reused for this long; Stripe keeps it open for at least 30 minutes. */
    private const REUSE_MINUTES = 25;

    /** Asking Stripe again when the player keeps refreshing: at most this often per payment. */
    private const CHECK_EVERY_SECONDS = 5;

    public function __construct(
        private readonly PaymentGateways $gateways,
        private readonly PaymentProcessor $processor = new PaymentProcessor,
    ) {}

    /**
     * The payment page for a booking that is waiting for payment: the one already open, or a new one
     * (e.g. after a declined card). Refused when the booking isn't waiting any more.
     */
    public function start(Booking $booking): Payment
    {
        if ($booking->status !== BookingStatus::PendingPayment || $booking->hold_expires_at === null || $booking->hold_expires_at->isPast()) {
            throw new ApiException('NOT_AWAITING_PAYMENT', 'This booking is not waiting for payment any more.');
        }
        $account = $booking->venue->paymentAccount;
        if ($account === null || ! $account->isReady()) {
            throw new ApiException('ONLINE_PAYMENT_UNAVAILABLE', 'This venue doesn\'t take online payment right now. Please pay at the counter or call the venue.');
        }

        $open = $booking->payments()
            ->where('status', PaymentStatus::Pending->value)
            ->whereNotNull('checkout_url')
            ->where('created_at', '>', now()->subMinutes(self::REUSE_MINUTES))
            ->latest('id')
            ->first();
        if ($open !== null) {
            return $open;
        }

        $attempt = $booking->payments()->count() + 1;
        $checkout = $this->gateways->make()->createCheckout($account->account_id, new CheckoutRequest(
            bookingCode: $booking->code,
            amountSen: $booking->total_sen,
            description: $this->describe($booking),
            customerEmail: (string) $booking->user?->email,
            returnUrl: rtrim((string) config('app.frontend_url'), '/')."/bookings/{$booking->code}/status",
            expiresAt: CarbonImmutable::parse($booking->hold_expires_at),
            // Two taps at once ask for the same attempt, and the gateway gives the same page for both.
            idempotencyKey: "courtku-booking-{$booking->id}-payment-{$attempt}",
        ));

        $payment = new Payment;
        $payment->forceFill([
            'booking_id' => $booking->id,
            'venue_id' => $booking->venue_id,
            'gateway' => $account->gateway,
            'account_id' => $account->account_id,
            'checkout_id' => $checkout->id,
            'checkout_url' => $checkout->url,
            'amount_sen' => $booking->total_sen,
            'currency' => 'myr',
            'status' => PaymentStatus::Pending,
        ]);
        try {
            $payment->save();
        } catch (UniqueConstraintViolationException) {
            // The other tap saved the same page a moment ago.
            return Payment::where('checkout_id', $checkout->id)->firstOrFail();
        }

        return $payment;
    }

    /**
     * Asks the gateway about this booking's payments that are still pending, e.g. when the player
     * comes back from the payment page. Quiet when the gateway can't be reached: the webhook, or
     * the next look, settles it.
     */
    public function refresh(Booking $booking): void
    {
        $pending = $booking->payments()
            ->where('status', PaymentStatus::Pending->value)
            ->where(fn ($query) => $query->whereNull('checked_at')->orWhere('checked_at', '<', now()->subSeconds(self::CHECK_EVERY_SECONDS)))
            ->get();

        foreach ($pending as $payment) {
            $payment->forceFill(['checked_at' => now()])->save();
            try {
                $this->processor->applyCheckout($payment, $this->gateways->make()->findCheckout($payment->account_id, $payment->checkout_id));
            } catch (PaymentGatewayException) {
                // Try again on the next look.
            }
        }
    }

    /** "Arena Demo: Court 1, Fri 6 Nov, 6:00 pm to 7:00 pm (CK-7F3K2)": what the player sees on Stripe's page. */
    private function describe(Booking $booking): string
    {
        $start = $booking->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE);
        $end = $booking->ends_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE);

        return "{$booking->venue->name}: {$booking->court->name}, {$start->format('D j M, g:i a')} to {$end->format('g:i a')} ({$booking->code})";
    }
}
