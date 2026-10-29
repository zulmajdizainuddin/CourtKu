<?php

namespace App\Support;

/**
 * Malaysian phone numbers in one format, +60 then the number without its leading 0,
 * which is also what the payment gateway expects (plan section 10).
 * The web app has the same rule in lib/phone.ts.
 */
final class MalaysianPhone
{
    /**
     * "012-345 6789", "+6012 3456789" or "03-1234 5678" -> "+60123456789" / "+60312345678".
     * Returns null for anything that isn't a Malaysian number.
     */
    public static function normalize(string $input): ?string
    {
        $number = (string) preg_replace('/[\s\-().]/', '', $input);

        $national = match (true) {
            str_starts_with($number, '+60') => substr($number, 3),
            str_starts_with($number, '60') => substr($number, 2),
            str_starts_with($number, '0') => substr($number, 1),
            default => null,
        };

        // After the 0: 8 to 10 digits, never starting with 0 (e.g. 12 345 6789, 3 1234 5678).
        if ($national === null || preg_match('/^[1-9]\d{7,9}$/', $national) !== 1) {
            return null;
        }

        return '+60'.$national;
    }
}
