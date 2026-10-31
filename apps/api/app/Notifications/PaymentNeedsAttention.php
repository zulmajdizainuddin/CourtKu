<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\Payment;
use App\Support\MalaysiaTime;
use App\Support\Money;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Money came in online but couldn't simply confirm the booking (plan section 9: amount mismatch, late
 * payment, paid after a cancel, paid twice). The owner gets the details to sort it out, usually a refund
 * (FR-25); the player is told the venue will refund them.
 */
class PaymentNeedsAttention extends Notification
{
    public function __construct(
        private readonly Payment $payment,
        private readonly Booking $booking,
        private readonly bool $forOwner,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        $start = $booking->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->format('D j M, g:i a');
        $what = "{$booking->code}: {$booking->court->name}, {$start}";
        $paid = Money::format((int) $this->payment->paid_amount_sen);
        $why = $this->payment->problem?->describe() ?? '';
        $frontend = rtrim((string) config('app.frontend_url'), '/');

        if ($this->forOwner) {
            return (new MailMessage)
                ->subject("A payment at {$booking->venue->name} needs your attention")
                ->line("{$paid} was paid online for booking {$what}.")
                ->line($why)
                ->line('Please check it in your Stripe dashboard and refund the player if needed, then record the refund in CourtKu.')
                ->action('Open Payments', "{$frontend}/dashboard/{$booking->venue_id}/payments");
        }

        return (new MailMessage)
            ->subject("About your payment for {$booking->code}")
            ->line("We received your payment of {$paid} for {$what} at {$booking->venue->name}.")
            ->line($why)
            ->line('The venue has been told and will refund you. Questions? Contact the venue with your booking code.')
            ->action('View your booking', "{$frontend}/bookings/{$booking->code}/status");
    }
}
