<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Puts a venue's courts or photos in the owner's chosen order.
 * The owner sends every id once, first to last; anything else (an id missing,
 * or one from another venue) is refused, so a list can never end up half sorted.
 */
final class SortOrder
{
    /**
     * @param  HasMany<covariant \Illuminate\Database\Eloquent\Model, covariant \Illuminate\Database\Eloquent\Model>  $items
     * @param  list<int>  $ids
     */
    public static function apply(HasMany $items, array $ids, string $field): void
    {
        $existing = $items->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $sent = collect($ids)->sort()->values()->all();

        if ($sent !== $existing) {
            throw ValidationException::withMessages([$field => 'Send every item of this venue exactly once.']);
        }

        DB::transaction(function () use ($items, $ids) {
            foreach ($ids as $position => $id) {
                $items->clone()->whereKey($id)->update(['sort_order' => $position]);
            }
        });
    }
}
