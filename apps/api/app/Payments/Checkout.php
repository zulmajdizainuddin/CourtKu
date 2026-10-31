<?php

namespace App\Payments;

use Carbon\CarbonImmutable;

/** One payment page at the gateway, as the gateway reports it. */
final readonly class Checkout
{
    public function __construct(
        public string $id, // e.g. "cs_test_a1B2..."
        public ?string $url, // where the player pays; null once it is closed
        public CheckoutStatus $status,
        public bool $paid, // true only when the money was taken
        public int $amountSen, // what the gateway charges (or charged)
        public string $currency, // "myr"
        public ?string $bookingCode,
        public ?CarbonImmutable $expiresAt,
    ) {}
}
