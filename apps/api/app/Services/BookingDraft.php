<?php

namespace App\Services;

use App\Models\Court;
use Carbon\CarbonImmutable;

/**
 * A booking that passed every check and has its price, but is not saved yet.
 * BookingService::prepare() makes it; BookingService::store() saves it.
 */
final class BookingDraft
{
    /**
     * @param  list<array{starts_at: CarbonImmutable, price_sen: int}>  $cells  each half hour, in UTC
     */
    public function __construct(
        public readonly Court $court,
        public readonly CarbonImmutable $startsAt,
        public readonly CarbonImmutable $endsAt,
        public readonly CarbonImmutable $businessDate,
        public readonly array $cells,
        public readonly int $totalSen,
    ) {}
}
