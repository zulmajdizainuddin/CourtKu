<?php

namespace App\Enums;

/**
 * A user's role at one venue. Roles live on the membership, not the user,
 * so the same person can be staff at one venue and owner at another.
 */
enum VenueRole: string
{
    case Owner = 'owner';
    case Staff = 'staff';
}
