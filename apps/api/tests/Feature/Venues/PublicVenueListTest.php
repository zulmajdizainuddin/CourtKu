<?php

use App\Models\Court;
use App\Models\Venue;

/*
| ADR-031: the venues guests can book, listed on the home page.
*/

/** An approved venue with courts [name, sport, price per hour in sen]. */
function listedVenue(string $name, string $state, string $city, array $courts, bool $demo = false, string $status = 'active'): Venue
{
    $venue = Venue::factory()->create(['name' => $name, 'slug' => str($name)->slug()->value(), 'state' => $state, 'city' => $city]);
    $venue->forceFill(['status' => $status, 'approved_at' => now(), 'is_demo' => $demo])->save();
    foreach ($courts as [$court, $sport, $price]) {
        Court::factory()->for($venue)->offering($sport)->create(['name' => $court, 'base_price_sen' => $price]);
    }

    return $venue;
}

function listNames(array $query = []): array
{
    return collect(test()->getJson('/api/v1/venues?'.http_build_query($query))->assertOk()->json('data'))->pluck('name')->all();
}

it('ADR-031 lists the venues guests can book, real ones first, then demo venues', function () {
    listedVenue('Zeta Hall', 'selangor', 'Shah Alam', [['Court 1', 'badminton', 2000]]);
    listedVenue('Alpha Arena', 'johor', 'Johor Bahru', [['Court 1', 'badminton', 1500]], demo: true);
    listedVenue('Beta Courts', 'pulau-pinang', 'George Town', [['Court 1', 'pickleball', 2500]]);
    // Not listed: waiting for approval, suspended, or nothing anyone can book yet.
    listedVenue('Pending Place', 'selangor', 'Klang', [['Court 1', 'badminton', 2000]], status: 'pending');
    listedVenue('Suspended Spot', 'selangor', 'Klang', [['Court 1', 'badminton', 2000]], status: 'suspended');
    listedVenue('No Price Yet', 'selangor', 'Klang', [['Court 1', 'badminton', null]]);

    expect(listNames())->toBe(['Beta Courts', 'Zeta Hall', 'Alpha Arena']);
});

it('shows what a card needs: where, sports, the lowest price and a cover photo', function () {
    $venue = listedVenue('Rally Hall', 'kuala-lumpur', 'Cheras', [
        ['Court 1', 'badminton', 2200], ['Court 2', 'badminton', 1800], ['Pitch', 'futsal', 9000],
    ]);
    $venue->photos()->create(['public_id' => 'courtku/rally-cover', 'width' => 1600, 'height' => 1200, 'sort_order' => 0]);

    $this->getJson('/api/v1/venues')
        ->assertJsonPath('data.0', [
            'name' => 'Rally Hall',
            'slug' => 'rally-hall',
            'city' => 'Cheras',
            'state' => 'kuala-lumpur',
            'is_demo' => false,
            'sports' => ['badminton', 'futsal'],
            'main_sport' => 'badminton', // 2 of its 3 courts
            'from_price_sen' => 1800,
            'from_price' => 'RM 18.00',
            'cover_url' => 'https://res.cloudinary.com/'.config('services.cloudinary.cloud_name').'/image/upload/c_fill,w_600,h_400,f_auto,q_auto/courtku/rally-cover',
        ]);

    // With a sport chosen, the price is that sport's.
    $this->getJson('/api/v1/venues?sport=futsal')->assertJsonPath('data.0.from_price_sen', 9000);
});

it('narrows the list by sport, state and a search for the name or city', function () {
    listedVenue('Shuttle House', 'kuala-lumpur', 'Cheras', [['Court 1', 'badminton', 2000]]);
    listedVenue('Kickoff Arena', 'selangor', 'Shah Alam', [['Pitch', 'futsal', 8000]]);
    listedVenue('Dink Club', 'pulau-pinang', 'George Town', [['Court 1', 'pickleball', 2500]]);

    expect(listNames(['sport' => 'futsal']))->toBe(['Kickoff Arena'])
        ->and(listNames(['state' => 'pulau-pinang']))->toBe(['Dink Club'])
        ->and(listNames(['q' => 'cheras']))->toBe(['Shuttle House']) // the city
        ->and(listNames(['q' => 'kick']))->toBe(['Kickoff Arena']) // the name
        ->and(listNames(['q' => '%']))->toBe([]) // % is a plain character, not "anything"
        ->and(listNames(['sport' => 'badminton', 'state' => 'selangor']))->toBe([]);
});

it('refuses filters that make no sense', function () {
    $this->getJson('/api/v1/venues?sport=chess')->assertStatus(422);
    $this->getJson('/api/v1/venues?state=atlantis')->assertStatus(422);
    $this->getJson('/api/v1/venues?q='.str_repeat('a', 61))->assertStatus(422);
});

it('shows 24 venues a page', function () {
    foreach (range(1, 25) as $n) {
        listedVenue(sprintf('Venue %02d', $n), 'selangor', 'Klang', [['Court 1', 'badminton', 2000]]);
    }

    $this->getJson('/api/v1/venues')->assertJsonCount(24, 'data')->assertJsonPath('meta.last_page', 2);
    $this->getJson('/api/v1/venues?page=2')->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Venue 25');
});
