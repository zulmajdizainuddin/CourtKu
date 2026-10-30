<?php

namespace App\Enums;

/** How a booking was made: by the player online, or by staff for someone at the counter or on the phone (FR-20). */
enum BookingSource: string
{
    case Online = 'online';
    case WalkIn = 'walk_in';
    case Phone = 'phone';
}
