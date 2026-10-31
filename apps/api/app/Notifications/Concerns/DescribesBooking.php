<?php

namespace App\Notifications\Concerns;

use App\Enums\PaymentMethod;
use App\Models\Booking;
use App\Support\MalaysiaTime;
use App\Support\Money;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * The parts every booking email shares (FR-26, FR-27): when, where and what it costs, in Malaysia time.
 * Each email is queued (ADR-027): tried again after 1 and 5 minutes if the email server fails.
 */
trait DescribesBooking
{
    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300];

    /** "Sat 7 Nov" */
    protected function day(Booking $booking): string
    {
        return $booking->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->format('D j M');
    }

    /** "Sat 7 Nov 2026, 8:00 pm to 10:00 pm" */
    protected function when(Booking $booking): string
    {
        $start = $booking->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE);
        $end = $booking->ends_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE);

        return $start->format('D j M Y, g:i a').' to '.$end->format('g:i a');
    }

    /** "Court 1 (Badminton)" */
    protected function court(Booking $booking): string
    {
        return $booking->court->name.($booking->sport ? " ({$booking->sport->name})" : '');
    }

    protected function paidOnline(Booking $booking): bool
    {
        return $booking->paid_at !== null && $booking->payment_method === PaymentMethod::Online;
    }

    /** The lines every booking email starts with: code, venue, court, time and price. */
    protected function details(MailMessage $mail, Booking $booking): MailMessage
    {
        $venue = $booking->venue;
        $address = implode(', ', array_filter([$venue->address_line, trim("{$venue->postcode} {$venue->city}")]));
        $total = Money::format($booking->total_sen);

        return $mail
            ->line("**Booking code:** {$booking->code}")
            ->line("**Venue:** {$venue->name}".($address !== '' ? ", {$address}" : ''))
            ->line("**Court:** {$this->court($booking)}")
            ->line("**When:** {$this->when($booking)}")
            ->line('**Price:** '.match (true) {
                $this->paidOnline($booking) => "{$total}, paid online",
                $booking->paid_at !== null => "{$total}, paid at the venue",
                default => "{$total}, to pay at the venue",
            });
    }

    /** The web app's address, for buttons in the email. */
    protected function frontend(string $path): string
    {
        return rtrim((string) config('app.frontend_url'), '/').$path;
    }
}
