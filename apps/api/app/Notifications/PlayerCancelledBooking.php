<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Concerns\DescribesBooking;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** FR-27: to the venue's owners and staff, when a player cancels their own booking: the time is free again. */
class PlayerCancelledBooking extends Notification implements ShouldQueue
{
    use DescribesBooking;
    use Queueable;

    public function __construct(public readonly Booking $booking) {}

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
            ->subject("Cancelled by the player: {$booking->court->name}, {$this->day($booking)} ({$booking->code})")
            ->greeting("Hi {$notifiable->name},")
            ->line("{$booking->user?->name} cancelled their booking. The time is free for others to book again.");

        $this->details($mail, $booking);

        // FR-25: refunds are made in Stripe by the owner, then recorded in CourtKu.
        if ($this->paidOnline($booking)) {
            return $mail
                ->line('They paid '.Money::format($booking->total_sen).' online. Please refund them in your Stripe dashboard, then record the refund on the Payments tab.')
                ->action('Open Payments', $this->frontend("/dashboard/{$booking->venue_id}/payments"));
        }

        return $mail
            ->action('Open the schedule', $this->frontend("/dashboard/{$booking->venue_id}/schedule?date={$booking->business_date->toDateString()}"))
            ->line('The venue owner can switch these emails off on the Settings tab.');
    }
}
