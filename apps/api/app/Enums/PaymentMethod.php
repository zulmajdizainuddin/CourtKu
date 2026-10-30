<?php

namespace App\Enums;

/** How a player paid at the counter (FR-28). Online payment (FPX) arrives in M4. */
enum PaymentMethod: string
{
    case Cash = 'cash';
    case Qr = 'qr'; // DuitNow QR or an e-wallet
    case Card = 'card';
}
