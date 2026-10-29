<?php

namespace App\Enums;

/**
 * Where a venue is in its life (FR-04).
 * A new venue is pending; only an admin can make it active (public) or suspend it.
 */
enum VenueStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';

    /** Approving works on a new venue and on a suspended one (to reinstate it). */
    public function canBeApproved(): bool
    {
        return $this !== self::Active;
    }

    /** Suspending works on a live venue, and on a pending one to turn it down. */
    public function canBeSuspended(): bool
    {
        return $this !== self::Suspended;
    }
}
