<?php

use App\Models\PriceRule;

/** Only the fields that matter here: which courts, which days, from and to. */
function ruleFor(?int $courtId, array $weekdays, string $from, string $to, ?string $name = null): PriceRule
{
    return new PriceRule([
        'court_id' => $courtId,
        'name' => $name,
        'weekdays' => $weekdays,
        'starts_at' => $from,
        'ends_at' => $to,
        'ends_next_day' => PriceRule::minutes($to) < PriceRule::minutes($from),
        'price_sen' => 3000,
    ]);
}

it('ADR-020 finds two rules for the same courts that cover the same half hour', function (PriceRule $other, bool $clashes) {
    $weekdayPeak = ruleFor(null, [1, 2, 3, 4, 5], '18:00', '00:00');

    expect($weekdayPeak->clashesWith($other))->toBe($clashes)
        ->and($other->clashesWith($weekdayPeak))->toBe($clashes);
})->with([
    'overlapping hours on Friday' => [fn () => ruleFor(null, [5, 6], '20:00', '22:00'), true],
    'running past midnight into the peak' => [fn () => ruleFor(null, [3], '23:30', '01:00'), true],
    'touching: ends when the peak starts' => [fn () => ruleFor(null, [1], '08:00', '18:00'), false],
    'other days' => [fn () => ruleFor(null, [6, 0], '18:00', '00:00'), false],
    'one court: its own rule wins' => [fn () => ruleFor(3, [1], '18:00', '00:00'), false],
]);

it('lets rules for two different courts cover the same hours', function () {
    expect(ruleFor(3, [1], '18:00', '00:00')->clashesWith(ruleFor(4, [1], '18:00', '00:00')))->toBeFalse()
        ->and(ruleFor(3, [1], '18:00', '00:00')->clashesWith(ruleFor(3, [1], '19:00', '20:00')))->toBeTrue();
});

it('writes days the short way', function (array $weekdays, string $text) {
    expect(PriceRule::formatWeekdays($weekdays))->toBe($text);
})->with([
    [[1, 2, 3, 4, 5], 'Mon–Fri'],
    [[6, 0], 'Sat, Sun'],
    [[0, 1, 2, 3, 4, 5, 6], 'Every day'],
    [[1, 3, 5], 'Mon, Wed, Fri'],
    [[5, 6, 0], 'Fri–Sun'],
    [[1, 2, 4, 5, 6], 'Mon, Tue, Thu–Sat'],
]);

it('describes a rule for error messages', function () {
    expect(ruleFor(null, [1, 2, 3, 4, 5], '18:00', '00:00', 'Weekday peak')->describe())
        ->toBe('Weekday peak (Mon–Fri, 6:00 pm – midnight)')
        ->and(ruleFor(null, [5], '18:30', '02:00')->describe())->toBe('Fri, 6:30 pm – 2:00 am (next day)')
        ->and(ruleFor(null, [6, 0], '08:00', '12:00')->describe())->toBe('Sat, Sun, 8:00 am – 12:00 pm');
});
