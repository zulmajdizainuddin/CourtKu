<?php

namespace App\Models\Concerns;

use App\Models\Venue;
use App\Services\AvailabilityCache;
use Illuminate\Database\Eloquent\Model;

/**
 * For models that shape a venue's free times or prices: saving or deleting one makes the venue's
 * kept time grid out of date (AvailabilityCache, ADR-028).
 *
 * Changes made with a plain query (e.g. update() on many rows at once) send no model events; the
 * code that makes them calls AvailabilityCache::forget() itself.
 */
trait ClearsAvailabilityCache
{
    protected static function bootClearsAvailabilityCache(): void
    {
        $clear = fn (Model $model) => AvailabilityCache::forget((int) ($model instanceof Venue ? $model->getKey() : $model->getAttribute('venue_id')));

        static::saved($clear);
        static::deleted($clear);
    }
}
