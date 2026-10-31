<?php

namespace App\Payments;

/** A signed webhook from the gateway, already checked and read. */
final readonly class PaymentEvent
{
    public function __construct(
        public string $id, // the gateway's event id, unique: the same event twice is done only once
        public PaymentEventType $type,
        public Checkout $checkout,
        public ?string $accountId = null, // which venue's account it happened on (Stripe Connect)
    ) {}
}
