<?php

namespace App\Enums;

/** How a player paid: at the counter (FR-28), or online through the venue's Stripe account (FR-22). */
enum PaymentMethod: string
{
    case Cash = 'cash';
    case Qr = 'qr'; // DuitNow QR or an e-wallet
    case Card = 'card';
    case Online = 'online'; // set by the payment itself, never by staff

    /**
     * What staff can choose at the counter.
     *
     * @return list<self>
     */
    public static function atCounter(): array
    {
        return [self::Cash, self::Qr, self::Card];
    }
}
