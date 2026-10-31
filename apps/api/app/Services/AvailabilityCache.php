<?php

namespace App\Services;

use App\Models\Venue;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Keeps the public time grid (GET /venues/{slug}/availability) for 30 seconds (ADR-028), so a busy
 * booking page asks the database once instead of once per visitor.
 *
 * Each venue has a "version": a random word that changes whenever something that shapes its free
 * times or prices is saved (a booking, a cancel, a block, hours, closures, courts, prices, settings:
 * see Models\Concerns\ClearsAvailabilityCache). A kept grid is only used while its version still
 * matches, so a change shows at once instead of after 30 seconds.
 *
 * The same version tells the staff schedule when to reload (FR-14, ScheduleController::version).
 *
 * The version is read BEFORE the grid is worked out. If a change lands while it is being worked out,
 * the grid is kept under the old version and simply not used again.
 */
final class AvailabilityCache
{
    public const SECONDS = 30;

    /**
     * @param  Closure(): array<string, mixed>  $build
     * @return array<string, mixed>
     */
    public static function remember(Venue $venue, string $date, string $sport, Closure $build): array
    {
        // One key per venue, day and sport, overwritten each time, so old copies never pile up.
        $key = "availability:{$venue->id}:{$date}:{$sport}";
        $version = self::version($venue->id);

        $kept = Cache::get($key);
        if (is_array($kept) && ($kept['version'] ?? null) === $version) {
            return $kept['data'];
        }

        $data = $build();
        Cache::put($key, ['version' => $version, 'data' => $data], self::SECONDS);

        return $data;
    }

    /** Something that changes the venue's times or prices was saved: stop using its kept grids, once the change is saved. */
    public static function forget(int $venueId): void
    {
        DB::afterCommit(fn () => Cache::forever(self::versionKey($venueId), Str::random(16)));
    }

    /** The venue's version now. The staff schedule asks for it every few seconds to know when to reload (FR-14). */
    public static function version(int $venueId): string
    {
        return (string) Cache::get(self::versionKey($venueId), 'first');
    }

    private static function versionKey(int $venueId): string
    {
        return "availability:{$venueId}:version";
    }
}
