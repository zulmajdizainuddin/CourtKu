<?php

namespace App\Payments;

/** How a venue's account at the gateway stands, shown to the owner on the Payments tab. */
final readonly class GatewayAccount
{
    public function __construct(
        public string $id, // e.g. "acct_1Abc..."; not a secret
        public ?string $name,
        public ?string $country, // "MY"
        public bool $chargesEnabled, // true = it can take payments now
    ) {}
}
