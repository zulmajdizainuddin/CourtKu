<?php

namespace App\Payments;

/** The webhook events CourtKu acts on. */
enum PaymentEventType: string
{
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';
}
