<?php

use App\Enums\VenueStatus;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;

beforeEach(function () {
    // 1 Nov 2026, 10:00 in Malaysia.
    $this->travelTo('2026-11-01 02:00:00');
    config(['services.cloudinary.cloud_name' => 'courtku-test']);
});

it('FR-06 shows an approved venue to anyone, without logging in', function () {
    $venue = Venue::factory()->active()->create([
        'name' => 'Arena Sg Buloh', 'slug' => 'arena-sg-buloh', 'city' => 'Sungai Buloh',
        'phone' => '+60123456789', 'facilities' => ['parking', 'surau'],
    ]);
    $venue->operatingHours()->create(['weekday' => 5, 'opens_at' => '08:00', 'closes_at' => '02:00', 'closes_next_day' => true]);
    $venue->photos()->create(['public_id' => 'courtku/venues/1/cover', 'width' => 1600, 'height' => 1200]);
    Court::factory()->for($venue)->offering('badminton', 'futsal')->create(['name' => 'Hall A']);

    $this->getJson('/api/v1/venues/arena-sg-buloh')
        ->assertOk()
        ->assertJsonPath('data.name', 'Arena Sg Buloh')
        ->assertJsonPath('data.city', 'Sungai Buloh')
        ->assertJsonPath('data.facilities', ['parking', 'surau'])
        ->assertJsonPath('data.opening_hours.0.closes_next_day', true)
        ->assertJsonPath('data.courts.0.name', 'Hall A')
        ->assertJsonPath('data.courts.0.sports', ['badminton', 'futsal'])
        ->assertJsonPath('data.photos.0.url', 'https://res.cloudinary.com/courtku-test/image/upload/c_limit,w_1600,f_auto,q_auto/courtku/venues/1/cover');
});

it('FR-04 hides venues that are waiting for approval or suspended, like an address that does not exist', function (VenueStatus $status) {
    Venue::factory()->create(['slug' => 'hidden-arena', 'status' => $status]);

    $this->getJson('/api/v1/venues/hidden-arena')->assertNotFound()->assertJsonPath('error.code', 'NOT_FOUND');
    $this->getJson('/api/v1/venues/hidden-arena/courts')->assertNotFound();
})->with([VenueStatus::Pending, VenueStatus::Suspended]);

it('answers 404 for a web address no venue uses', function () {
    $this->getJson('/api/v1/venues/no-such-venue')->assertNotFound();
});

it('never shows the owner, staff, emails or other private details', function () {
    $owner = User::factory()->create(['email' => 'owner@example.com']);
    Venue::factory()->active()->ownedBy($owner)->create(['slug' => 'arena', 'is_demo' => true]);

    $response = $this->getJson('/api/v1/venues/arena')->assertOk();

    expect(array_keys($response->json('data')))->not->toContain('id', 'status', 'role', 'is_demo', 'approved_at', 'owners')
        ->and($response->getContent())->not->toContain('owner@example.com');
});

it('shows only active courts and only closures that are not over yet', function () {
    $venue = Venue::factory()->active()->create(['slug' => 'arena']);
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1']);
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Under repair', 'is_active' => false]);
    $venue->closures()->create(['starts_on' => '2026-10-01', 'ends_on' => '2026-10-02', 'reason' => 'Already over']);
    $venue->closures()->create(['starts_on' => '2026-12-24', 'ends_on' => '2026-12-26', 'reason' => 'Christmas']);

    $this->getJson('/api/v1/venues/arena')
        ->assertJsonCount(1, 'data.courts')
        ->assertJsonPath('data.courts.0.name', 'Court 1')
        ->assertJsonCount(1, 'data.closures')
        ->assertJsonPath('data.closures.0.reason', 'Christmas');

    $this->getJson('/api/v1/venues/arena/courts')->assertJsonCount(1, 'data');
});
