<?php

use App\Support\MalaysianPhone;

it('stores Malaysian numbers in one +60 format, however they are typed', function (string $typed, string $stored) {
    expect(MalaysianPhone::normalize($typed))->toBe($stored);
})->with([
    'mobile with dashes' => ['012-345 6789', '+60123456789'],
    'mobile, 11 digits' => ['011-2345 6789', '+601123456789'],
    'Klang Valley landline' => ['03-1234 5678', '+60312345678'],
    'already +60' => ['+60 12-345 6789', '+60123456789'],
    'country code without +' => ['60123456789', '+60123456789'],
    'with brackets' => ['(03) 1234 5678', '+60312345678'],
]);

it('refuses what is not a Malaysian number', function (string $typed) {
    expect(MalaysianPhone::normalize($typed))->toBeNull();
})->with([
    'Singapore number' => ['+65 9123 4567'],
    'too short' => ['012-345'],
    'too long' => ['012-345 678 901'],
    'letters' => ['012-CALL-NOW'],
    'missing the leading 0' => ['123456789'],
    'double 0' => ['00123456789'],
]);
