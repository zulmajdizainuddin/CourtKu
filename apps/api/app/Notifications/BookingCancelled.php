<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Concerns\DescribesBooking;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** FR-26: to the player, when their confirmed booking is cancelled, by themselves or by the venue. */
class BookingCancelled extends Notification implements ShouldQueue
{
    use DescribesBooking;
    use Queueable;

    public function __construct(
        public readonly Booking $booking,
        public readonly bool $byPlayer,
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

        $mail = (new MailMessage)
            ->subject("Cancelled: {$booking->court->name} at {$booking->venue->name}, {$this->day($booking)}")
            ->greeting("Hi {$notifiable->name},");

        if ($this->byPlayer) {
            $mail->line('You cancelled this booking.');
        } else {
            $mail->line("{$booking->venue->name} cancelled your booking.");
            if ($booking->cancel_reason) {
                $mail->line("**Reason:** {$booking->cancel_reason}");
            }
        }

        $this->details($mail, $booking);

        if ($this->paidOnline($booking)) {
            $mail->line('You paid '.Money::format($booking->total_sen).' online. The venue will refund you; contact them with your booking code if you have questions.');
        }

        return $mail->action('Book another time', $this->frontend("/v/{$booking->venue->slug}/book"));
    }
}
