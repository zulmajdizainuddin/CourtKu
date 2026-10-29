<?php

namespace App\Enums;

/**
 * The 13 states and 3 federal territories, for the venue address.
 * Public holidays differ by state, so holiday rates (FR-11) will use this later.
 * The web app keeps the same list in lib/states.ts.
 */
enum MalaysianState: string
{
    case Johor = 'johor';
    case Kedah = 'kedah';
    case Kelantan = 'kelantan';
    case Melaka = 'melaka';
    case NegeriSembilan = 'negeri-sembilan';
    case Pahang = 'pahang';
    case Perak = 'perak';
    case Perlis = 'perlis';
    case PulauPinang = 'pulau-pinang';
    case Sabah = 'sabah';
    case Sarawak = 'sarawak';
    case Selangor = 'selangor';
    case Terengganu = 'terengganu';
    case KualaLumpur = 'kuala-lumpur';
    case Labuan = 'labuan';
    case Putrajaya = 'putrajaya';
}
