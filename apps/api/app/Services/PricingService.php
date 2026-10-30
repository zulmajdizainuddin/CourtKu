<?php

namespace App\Services;

use App\Models\Court;
use App\Models\PriceRule;
use App\Models\Venue;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Works out what a court costs (FR-09, FR-10). Plan section 7: "for each slot, take the
 * matching pricing rule, else the court's base price; sum in sen".
 *
 * Every half hour is priced on its own, from the most specific rule that covers it:
 *   1. a rule for this court,
 *   2. else a rule for all courts,
 *   3. else the court's normal price.
 * Rules for the same courts never overlap (ADR-020), so at most one rule of each kind matches.
 *
 * Times are "business day + minutes after that day's midnight" (ADR-015): at a venue open
 * until 2 am, Saturday 00:30 is Friday, minute 1470, and Friday's rules apply.
 * Prices are per hour in 10-sen steps (ADR-017), so half an hour is always whole sen.
 */
class PricingService
{
    /** Bookings are counted in 30-minute cells (ADR-010). */
    public const CELL_MINUTES = 30;

    /**
     * @param  Collection<int, PriceRule>  $rules  all of the venue's rules
     */
    public function __construct(private readonly Collection $rules) {}

    /** Loads the venue's rules once, for pricing many cells. */
    public static function forVenue(Venue $venue): self
    {
        return new self($venue->priceRules()->get());
    }

    /**
     * The price of one 30-minute cell, in sen.
     *
     * @param  int  $weekday  the business day, 0 = Sunday ... 6 = Saturday
     * @param  int  $minute  minutes after that business day's midnight (may be 1440 or more)
     */
    public function cellPriceSen(Court $court, int $weekday, int $minute): int
    {
        if ($court->base_price_sen === null) {
            throw new InvalidArgumentException("Court {$court->id} has no price yet, so it can't be priced or booked.");
        }

        $rule = $this->rules->first(fn (PriceRule $r) => $r->court_id !== null && $r->court_id === $court->id && $r->covers($weekday, $minute))
            ?? $this->rules->first(fn (PriceRule $r) => $r->court_id === null && $r->covers($weekday, $minute));

        $perHour = $rule === null ? $court->base_price_sen : $rule->price_sen;

        return intdiv($perHour, 60 / self::CELL_MINUTES);
    }

    /**
     * The total for a booking of $minutes starting at $startMinute on a business day,
     * adding up each half hour, e.g. 5 pm to 7 pm with peak from 6 pm = 2 normal + 2 peak cells.
     */
    public function priceSen(Court $court, int $weekday, int $startMinute, int $minutes): int
    {
        if ($minutes <= 0 || $minutes % self::CELL_MINUTES !== 0 || $startMinute % self::CELL_MINUTES !== 0) {
            throw new InvalidArgumentException('Bookings are whole 30-minute cells starting on :00 or :30.');
        }

        $total = 0;
        for ($minute = $startMinute; $minute < $startMinute + $minutes; $minute += self::CELL_MINUTES) {
            $total += $this->cellPriceSen($court, $weekday, $minute);
        }

        return $total;
    }
}
