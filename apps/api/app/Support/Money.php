<?php

namespace App\Support;

/**
 * Money is always whole sen (RM 25.00 = 2500), never a decimal number, so there are
 * no rounding errors (NFR-13). This only turns sen into text for people to read.
 */
final class Money
{
    /** 2500 -> "RM 25.00", 125050 -> "RM 1,250.50" */
    public static function format(int $sen): string
    {
        return 'RM '.number_format(intdiv($sen, 100)).'.'.str_pad((string) ($sen % 100), 2, '0', STR_PAD_LEFT);
    }
}
