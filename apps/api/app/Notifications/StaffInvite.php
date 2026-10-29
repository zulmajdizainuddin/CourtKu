<?php

namespace App\Notifications;

use App\Models\Venue;
use App\Models\VenueInvite;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The email that invites someone to work at a venue (FR-05).
 * Sent to an email address, not a user: the person may not have an account yet.
 */
class StaffInvite extends Notification
{
    public function __construct(
        private readonly Venue $venue,
        private readonly string $inviterName,
        private readonly string $token,
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
        return (new MailMessage)
            ->subject("Join {$this->venue->name} on CourtKu")
            ->line("{$this->inviterName} invited you to work at {$this->venue->name} on CourtKu, where you'll help run the venue's bookings.")
            ->action('Accept the invite', $this->link())
            ->line('Use this email address to log in or create your account; the invite only works for it.')
            ->line('The link works for '.VenueInvite::DAYS_VALID.' days. If you weren\'t expecting this, you can ignore this email.');
    }

    /** The web app's invite page, e.g. https://courtku.vercel.app/invites/abc123... */
    public function link(): string
    {
        return config('app.frontend_url').'/invites/'.$this->token;
    }
}
