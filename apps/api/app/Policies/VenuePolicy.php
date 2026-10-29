<?php

namespace App\Policies;

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
}
