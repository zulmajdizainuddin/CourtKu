<?php

namespace App\Payments;

use Carbon\CarbonImmutable;

/** What to charge for, and where the player goes afterwards. Amounts are in sen (NFR-13). */
final readonly class CheckoutRequest
{
    public function __construct(
        public string $bookingCode, // "CK-7F3K2", kept on the payment for matching it up later
        public int $amountSen,
        public string $description, // "Court booking CK-7F3K2: Court 1, Fri 6 Nov, 6:00 pm"
        public string $customerEmail,
        public string $returnUrl, // the web app's /bookings/{code}/status
        public CarbonImmutable $expiresAt,
        public string $idempotencyKey, // the same key twice = the same payment page, never two
    ) {}
}
