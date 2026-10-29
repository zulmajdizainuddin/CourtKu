<?php

use App\Support\OpeningHoursRules;

const SUNDAY = 0;
const FRIDAY = 5;
const SATURDAY = 6;

function period(int $weekday, string $opens, string $closes): array
{
    return ['weekday' => $weekday, 'opens_at' => $opens, 'closes_at' => $closes];
}

it('FR-37 knows a period that closes at or before it opens ends the next morning', function () {
    expect(OpeningHoursRules::closesNextDay('08:00', '23:00'))->toBeFalse()
        ->and(OpeningHoursRules::closesNextDay('08:00', '02:00'))->toBeTrue()
        ->and(OpeningHoursRules::closesNextDay('08:00', '00:00'))->toBeTrue() // midnight
        ->and(OpeningHoursRules::closesNextDay('00:00', '00:00'))->toBeTrue(); // open 24 hours

    // Friday 08:00 to Saturday 02:00, in minutes since Sunday 00:00.
    expect(OpeningHoursRules::span(period(FRIDAY, '08:00', '02:00')))->toBe([5 * 1440 + 480, 6 * 1440 + 120]);
});

it('accepts a normal week, late nights and a Friday prayer break', function () {
    expect(OpeningHoursRules::problems([
        period(FRIDAY, '08:00', '12:30'),
        period(FRIDAY, '14:30', '02:00'), // ADR-016: two periods, the second past midnight
        period(SATURDAY, '02:00', '10:00'), // starts exactly when Friday night ends
        period(SUNDAY, '00:00', '00:00'), // open 24 hours
    ]))->toBe([]);
});

it('ADR-016 allows at most two periods on one day', function () {
    expect(OpeningHoursRules::problems([
        period(FRIDAY, '08:00', '10:00'),
        period(FRIDAY, '11:00', '12:00'),
        period(FRIDAY, '13:00', '14:00'),
    ]))->toBe(['periods.2.weekday' => 'Friday can have at most two opening periods.']);
});

it('refuses periods that overlap on the same day', function () {
    expect(OpeningHoursRules::problems([
        period(FRIDAY, '08:00', '13:00'),
        period(FRIDAY, '12:30', '18:00'),
    ]))->toBe(['periods.1.opens_at' => 'Friday 12:30–18:00 overlaps Friday 08:00–13:00.']);
});

it('FR-37 refuses a late night that runs into the next day\'s opening', function () {
    expect(OpeningHoursRules::problems([
        period(FRIDAY, '08:00', '02:00'),
        period(SATURDAY, '01:00', '10:00'),
    ]))->toBe(['periods.1.opens_at' => 'Saturday 01:00–10:00 overlaps Friday 08:00–02:00.']);
});

it('also sees Saturday night running into Sunday morning', function () {
    expect(OpeningHoursRules::problems([
        period(SUNDAY, '01:00', '10:00'),
        period(SATURDAY, '20:00', '02:00'),
    ]))->toHaveKey('periods.1.opens_at');
});
