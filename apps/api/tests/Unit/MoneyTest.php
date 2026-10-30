<?php

use App\Support\Money;

it('NFR-13 writes whole sen as ringgit, without decimal maths', function (int $sen, string $text) {
    expect(Money::format($sen))->toBe($text);
})->with([
    [2500, 'RM 25.00'],
    [2550, 'RM 25.50'],
    [5, 'RM 0.05'],
    [0, 'RM 0.00'],
    [125050, 'RM 1,250.50'],
]);
