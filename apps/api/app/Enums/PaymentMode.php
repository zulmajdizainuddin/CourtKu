<?php

namespace App\Enums;

/**
 * How players pay at a venue (FR-17, FR-22). A venue starts on counter; online needs its own
 * connected payment account (ADR-009).
 */
enum PaymentMode: string
{
    case Counter = 'counter'; // pay at the venue; the booking is confirmed straight away
    case Online = 'online'; // pay online while the time is held (FR-16)
    case Both = 'both'; // the player chooses

    public function needsAccount(): bool
    {
        return $this !== self::Counter;
    }
}
