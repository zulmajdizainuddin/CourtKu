<?php

use Database\Seeders\DemoVenueSeeder;

it('seeds a public demo venue with 3 badminton, 1 futsal and 2 pickleball courts', function () {
    $this->seed(DemoVenueSeeder::class);

    $this->getJson('/api/v1/venues/arena-demo')
        ->assertOk()
        ->assertJsonPath('data.name', 'Arena Demo Sungai Buloh')
        ->assertJsonCount(6, 'data.courts')
        ->assertJsonCount(8, 'data.opening_hours')
        ->assertJsonPath('data.courts.3.sports', ['futsal'])
        ->assertJsonPath('data.courts.5.is_indoor', false);
});
