<?php

namespace Database\Seeders;

use App\Enums\MalaysianState;
use App\Enums\VenueStatus;
use App\Models\Court;
use App\Models\Sport;
use App\Models\Venue;
use Illuminate\Database\Seeder;

/**
 * A fictional, approved venue for local development (plan section 12): 3 badminton courts,
 * 1 futsal court and 2 pickleball courts, open late, with a Friday prayer break.
 * Its public page is http://localhost:3000/v/arena-demo
 */
class DemoVenueSeeder extends Seeder
{
    public function run(): void
    {
        $venue = new Venue([
            'name' => 'Arena Demo Sungai Buloh',
            'slug' => 'arena-demo',
            'state' => MalaysianState::Selangor,
            'description' => "A fictional venue for trying out CourtKu.\nWooden badminton courts, an indoor futsal pitch and two outdoor pickleball courts.",
            'address_line' => 'Lot 1, Jalan Contoh 1',
            'city' => 'Sungai Buloh',
            'postcode' => '47000',
            'phone' => '+60312345678',
            'facilities' => ['parking', 'surau', 'toilets', 'showers', 'canteen'],
        ]);
        // Not fillable on purpose (only admins approve), so set directly.
        $venue->forceFill(['status' => VenueStatus::Active, 'approved_at' => now(), 'is_demo' => true])->save();

        // Monday to Thursday and Sunday 8 am to midnight; Friday with a prayer break;
        // Saturday until 2 am (FR-37).
        foreach ([1, 2, 3, 4, 0] as $weekday) {
            $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '00:00', 'closes_next_day' => true]);
        }
        $venue->operatingHours()->create(['weekday' => 5, 'opens_at' => '08:00', 'closes_at' => '12:30']);
        $venue->operatingHours()->create(['weekday' => 5, 'opens_at' => '14:30', 'closes_at' => '00:00', 'closes_next_day' => true]);
        $venue->operatingHours()->create(['weekday' => 6, 'opens_at' => '08:00', 'closes_at' => '02:00', 'closes_next_day' => true]);

        $courts = [
            ['Court 1', true, 'badminton'],
            ['Court 2', true, 'badminton'],
            ['Court 3', true, 'badminton'],
            ['Futsal Pitch', true, 'futsal'],
            ['Pickleball 1', false, 'pickleball'],
            ['Pickleball 2', false, 'pickleball'],
        ];
        foreach ($courts as $order => [$name, $indoor, $sport]) {
            /** @var Court $court */
            $court = $venue->courts()->create(['name' => $name, 'is_indoor' => $indoor, 'sort_order' => $order]);
            $court->sports()->attach(Sport::where('slug', $sport)->value('id'));
        }
    }
}
