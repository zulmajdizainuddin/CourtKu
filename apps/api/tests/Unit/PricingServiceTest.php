<?php

use App\Models\Court;
use App\Models\PriceRule;
use App\Services\PricingService;

/*
| Times here are "business day + minutes after its midnight" (ADR-015):
| 18:00 = 1080, midnight = 1440, and 00:30 after a late Friday night = Friday, 1470.
*/

const MON = 1;
const FRI = 5;
const SAT = 6;

/** A court with an id (so court rules can find it) and a normal price per hour. */
function pricedCourt(int $id, ?int $basePriceSen = 2000): Court
{
    return (new Court)->forceFill(['id' => $id, 'base_price_sen' => $basePriceSen]);
}

/** A rule like the factory's: all courts, Mon–Fri, 6:00 pm to midnight, RM 30.00 an hour. */
function priceRule(array $overrides = []): PriceRule
{
    return new PriceRule([
        'court_id' => null,
        'name' => 'Weekday peak',
        'weekdays' => [1, 2, 3, 4, 5],
        'starts_at' => '18:00',
        'ends_at' => '00:00',
        'ends_next_day' => true,
        'price_sen' => 3000,
        ...$overrides,
    ]);
}

function pricing(PriceRule ...$rules): PricingService
{
    return new PricingService(collect($rules));
}

it('FR-09 charges the court\'s normal price when no rule matches', function () {
    // RM 20.00 an hour = RM 10.00 a half hour; 2 hours = RM 40.00.
    expect(pricing()->cellPriceSen(pricedCourt(1), MON, 600))->toBe(1000)
        ->and(pricing()->priceSen(pricedCourt(1), MON, 600, 120))->toBe(4000);
});

it('FR-10 charges an all-courts rule during its hours only', function () {
    $pricing = pricing(priceRule());

    expect($pricing->cellPriceSen(pricedCourt(1), MON, 1080))->toBe(1500) // Mon 6:00 pm: peak
        ->and($pricing->cellPriceSen(pricedCourt(1), MON, 1050))->toBe(1000) // Mon 5:30 pm: normal
        ->and($pricing->cellPriceSen(pricedCourt(1), SAT, 1080))->toBe(1000); // Saturday: not in the rule
});

it('ADR-020 lets a rule for one court beat a rule for all courts', function () {
    $pricing = pricing(
        priceRule(['price_sen' => 3000]),
        priceRule(['court_id' => 2, 'name' => 'Court 2 peak', 'price_sen' => 5000]),
    );

    expect($pricing->cellPriceSen(pricedCourt(2), MON, 1080))->toBe(2500) // its own rule
        ->and($pricing->cellPriceSen(pricedCourt(1), MON, 1080))->toBe(1500); // the all-courts rule
});

it('ADR-015 counts the hours after midnight as the night before', function () {
    // Friday 6:00 pm to 2:00 am: Saturday 00:30 on the clock is still Friday night.
    $pricing = pricing(priceRule(['weekdays' => [FRI], 'ends_at' => '02:00', 'price_sen' => 4000]));

    expect($pricing->cellPriceSen(pricedCourt(1), FRI, 1410))->toBe(2000) // Fri 11:30 pm
        ->and($pricing->cellPriceSen(pricedCourt(1), FRI, 1470))->toBe(2000) // Fri night, 00:30
        ->and($pricing->cellPriceSen(pricedCourt(1), FRI, 1560))->toBe(1000); // 2:00 am: the rule has ended
});

it('stops a rule that ends at midnight at midnight', function () {
    $pricing = pricing(priceRule());

    expect($pricing->cellPriceSen(pricedCourt(1), MON, 1410))->toBe(1500) // 11:30 pm
        ->and($pricing->cellPriceSen(pricedCourt(1), MON, 1440))->toBe(1000); // 00:00, back to normal
});

it('adds up each half hour of a booking that runs into peak time', function () {
    // Monday 5:00 pm to 7:00 pm: two normal half hours (RM 10.00) and two peak ones (RM 15.00).
    expect(pricing(priceRule())->priceSen(pricedCourt(1), MON, 1020, 120))->toBe(5000);
});

it('refuses to price a court without a price, or a booking that is not whole half hours', function () {
    expect(fn () => pricing()->cellPriceSen(pricedCourt(1, null), MON, 600))->toThrow(InvalidArgumentException::class)
        ->and(fn () => pricing()->priceSen(pricedCourt(1), MON, 600, 45))->toThrow(InvalidArgumentException::class)
        ->and(fn () => pricing()->priceSen(pricedCourt(1), MON, 615, 60))->toThrow(InvalidArgumentException::class);
});
