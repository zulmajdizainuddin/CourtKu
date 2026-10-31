<?php

namespace App\Payments;

/** Where a payment page stands at the gateway. */
enum CheckoutStatus: string
{
    case Open = 'open';
    case Complete = 'complete';
    case Expired = 'expired';
}
