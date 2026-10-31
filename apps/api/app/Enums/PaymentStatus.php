<?php

namespace App\Enums;

/** Where one online payment attempt stands (plan section 6, payments). */
enum PaymentStatus: string
{
    case Pending = 'pending'; // the player is on the payment page
    case Paid = 'paid'; // the money came in
    case Failed = 'failed'; // the bank or card said no; the player can try again while the hold lasts
    case Expired = 'expired'; // the payment page closed without payment
}
