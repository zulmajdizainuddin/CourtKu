<?php

namespace App\Models;

use App\Enums\VenueRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * One row in venue_members: this user works at this venue with this role.
 * Reached through $venue->members or $user->venues, as ->pivot.
 *
 * @property VenueRole $role
 */
class VenueMember extends Pivot
{
    protected $table = 'venue_members';

    // venue_members has its own id column, unlike a plain link table.
    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'role' => VenueRole::class,
        ];
    }
}
