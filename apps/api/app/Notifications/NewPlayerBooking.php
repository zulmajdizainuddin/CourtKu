<?php

namespace App\Notifications;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Notifications\Concerns\DescribesBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** FR-27: to the venue's owners and staff, for each booking a player makes on the website. */
class NewPlayerBooking extends Notification implements ShouldQueue
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

    /** Sent up to a minute later: if the player cancelled meanwhile, only the cancel email goes out. */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        return $this->booking->status !== BookingStatus::Cancelled;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        $player = $booking->user;

        $mail = (new MailMessage)
            ->subject("New booking: {$booking->court->name}, {$this->day($booking)} ({$booking->code})")
            ->greeting("Hi {$notifiable->name},")
            ->line("{$player?->name} booked a court at {$booking->venue->name}.");

        return $this->details($mail, $booking)
            ->line('**Player:** '.implode(', ', array_filter([$player?->name, $player?->email, $player?->phone])))
            ->action('Open the schedule', $this->frontend("/dashboard/{$booking->venue_id}/schedule?date={$booking->business_date->toDateString()}"))
            ->line('The venue owner can switch these emails off on the Settings tab.');
    }
}
