<?php

namespace App\Notifications;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Notifications\Concerns\DescribesBooking;
use App\Support\MalaysiaTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** FR-26: to the player, once their booking is confirmed (booked to pay at the venue, or paid online). */
class BookingConfirmed extends Notification implements ShouldQueue
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

    /** Sent up to a minute later: if the booking was cancelled meanwhile, only the cancel email goes out. */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        return $this->booking->status !== BookingStatus::Cancelled;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        $deadline = $booking->playerCancelDeadline()->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->format('D j M, g:i a');
        $phone = $booking->venue->phone ? " on {$booking->venue->phone}" : '';

        $mail = (new MailMessage)
            ->subject("Booked: {$booking->court->name} at {$booking->venue->name}, {$this->day($booking)}")
            ->greeting("Hi {$notifiable->name},")
            ->line('Your booking is confirmed.');

        return $this->details($mail, $booking)
            ->line("Need to cancel? You can cancel online until {$deadline}. After that, please call the venue{$phone}.")
            ->action('View your booking', $this->frontend("/bookings/{$booking->code}"))
            ->line('Show your booking code at the counter when you arrive.');
    }
}
