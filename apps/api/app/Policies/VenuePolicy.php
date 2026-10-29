<?php

namespace App\Policies;

use App\Enums\VenueRole;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Auth\Access\Response;

/**
 * Who may do what with a venue in the dashboard (ADR-002).
 * Laravel finds this class by its name (VenuePolicy for the Venue model).
 */
class VenuePolicy
{
    /**
     * Owners and staff can open the venue in their dashboard.
     * Anyone else gets 404, not 403, so they can't even tell the venue's dashboard exists.
     */
    public function view(User $user, Venue $venue): Response
    {
        return $venue->roleOf($user) !== null
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Only owners change the profile (and later courts, hours and prices).
     * Staff already know the venue exists, so they get a plain 403.
     */
    public function update(User $user, Venue $venue): Response
    {
        return match ($venue->roleOf($user)) {
            VenueRole::Owner => Response::allow(),
            VenueRole::Staff => Response::deny('Only the venue owner can change this.'),
            null => Response::denyAsNotFound(),
        };
    }
}
