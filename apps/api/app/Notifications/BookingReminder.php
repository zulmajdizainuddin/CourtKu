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

/** FR-26: "your game is tomorrow", to the player a day before the start (Reminders). */
class BookingReminder extends Notification implements ShouldQueue
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

    /** Sent up to a minute later: not if the booking was cancelled meanwhile. */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        return $this->booking->status === BookingStatus::Confirmed;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        $venue = $booking->venue;
        $start = $booking->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE);
        $phone = $venue->phone ? " on {$venue->phone}" : '';

        $mail = (new MailMessage)
            ->subject("Reminder: {$booking->court->name} at {$venue->name}, {$start->format('D j M, g:i a')}")
            ->greeting("Hi {$notifiable->name},")
            ->line("Don't forget your game at {$venue->name}, {$start->format('l')} at {$start->format('g:i a')}.");

        $this->details($mail, $booking);

        // FR-24: the cancel cutoff may still be ahead (e.g. 2 hours before) or already past (24 or 48).
        if ($booking->playerCanCancel()) {
            $deadline = $booking->playerCancelDeadline()->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->format('D j M, g:i a');
            $mail->line("Can't make it? Please cancel online before {$deadline}, so someone else can play.");
        } else {
            $mail->line("Can't make it? Please call the venue{$phone}, so someone else can play.");
        }

        $directions = 'https://www.google.com/maps/search/?api=1&query='
            .rawurlencode(implode(', ', array_filter([$venue->name, $venue->address_line, trim("{$venue->postcode} {$venue->city}"), 'Malaysia'])));

        return $mail
            ->action('View your booking', $this->frontend("/bookings/{$booking->code}"))
            ->line("Getting there: [directions on Google Maps]({$directions})");
    }
}
