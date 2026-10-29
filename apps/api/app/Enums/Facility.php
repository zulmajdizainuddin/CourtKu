<?php

namespace App\Enums;

/**
 * What a venue offers besides its courts (FR-06). A fixed list, so every venue page
 * shows the same names and players can compare venues.
 * The web app keeps the same list, with labels, in lib/facilities.ts.
 */
enum Facility: string
{
    case Parking = 'parking';
    case Surau = 'surau';
    case Toilets = 'toilets';
    case Showers = 'showers';
    case ChangingRooms = 'changing_rooms';
    case Lockers = 'lockers';
    case Canteen = 'canteen';
    case Wifi = 'wifi';
    case AirConditioning = 'air_conditioning';
    case EquipmentRental = 'equipment_rental';
}
