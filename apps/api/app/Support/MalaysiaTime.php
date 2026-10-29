<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * The server and database run in UTC (NFR-12), but venues live on Malaysia time.
 * "Today" for a venue is today in Malaysia: at 11 pm UTC it is already tomorrow there.
 */
final class MalaysiaTime
{
    public const TIMEZONE = 'Asia/Kuala_Lumpur';

    /** Today's date in Malaysia, e.g. "2026-10-30". */
    public static function today(): string
    {
        return Carbon::now(self::TIMEZONE)->toDateString();
    }
}
