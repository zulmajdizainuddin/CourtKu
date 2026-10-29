<?php

use App\Models\User;
use App\Models\Venue;

/** An owner (with two-factor) and their venue. */
function hoursOwnerAndVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create();

    return [$owner, Venue::factory()->ownedBy($owner)->create()];
}

it('FR-07 saves the week, including a late night and a Friday prayer break', function () {
    [$owner, $venue] = hoursOwnerAndVenue();

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/hours", ['periods' => [
        ['weekday' => 5, 'opens_at' => '14:30', 'closes_at' => '02:00'],
        ['weekday' => 5, 'opens_at' => '08:00', 'closes_at' => '12:30'],
        ['weekday' => 1, 'opens_at' => '08:00', 'closes_at' => '23:00'],
    ]])
        ->assertOk()
        ->assertJsonCount(3, 'data')
        // Sorted by weekday, then opening time.
        ->assertJsonPath('data.0', ['weekday' => 1, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false])
        ->assertJsonPath('data.1.opens_at', '08:00')
        ->assertJsonPath('data.2', ['weekday' => 5, 'opens_at' => '14:30', 'closes_at' => '02:00', 'closes_next_day' => true]);

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/hours")->assertJsonCount(3, 'data');
});

it('replaces the whole week on every save', function () {
    [$owner, $venue] = hoursOwnerAndVenue();
    $venue->operatingHours()->create(['weekday' => 2, 'opens_at' => '09:00', 'closes_at' => '17:00']);

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/hours", ['periods' => [
        ['weekday' => 3, 'opens_at' => '10:00', 'closes_at' => '22:00'],
    ]])->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.weekday', 3);

    // An empty list means closed every day.
    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/hours", ['periods' => []])
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('checks times and overlaps, and saves nothing when something is wrong', function (array $periods, string $field) {
    [$owner, $venue] = hoursOwnerAndVenue();
    $venue->operatingHours()->create(['weekday' => 2, 'opens_at' => '09:00', 'closes_at' => '17:00']);

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/hours", ['periods' => $periods])
        ->assertStatus(422)
        ->assertJsonValidationErrors($field, 'error.details.fields');

    expect($venue->operatingHours()->count())->toBe(1);
})->with([
    'not on the half hour (ADR-010)' => [[['weekday' => 1, 'opens_at' => '08:15', 'closes_at' => '22:00']], 'periods.0.opens_at'],
    'not a time' => [[['weekday' => 1, 'opens_at' => '08:00', 'closes_at' => '25:00']], 'periods.0.closes_at'],
    'no such weekday' => [[['weekday' => 7, 'opens_at' => '08:00', 'closes_at' => '22:00']], 'periods.0.weekday'],
    'overlap on one day' => [[
        ['weekday' => 1, 'opens_at' => '08:00', 'closes_at' => '13:00'],
        ['weekday' => 1, 'opens_at' => '12:00', 'closes_at' => '18:00'],
    ], 'periods.1.opens_at'],
    'late night into the next morning (FR-37)' => [[
        ['weekday' => 5, 'opens_at' => '08:00', 'closes_at' => '02:00'],
        ['weekday' => 6, 'opens_at' => '01:00', 'closes_at' => '10:00'],
    ], 'periods.1.opens_at'],
    'three periods on one day (ADR-016)' => [[
        ['weekday' => 1, 'opens_at' => '08:00', 'closes_at' => '10:00'],
        ['weekday' => 1, 'opens_at' => '11:00', 'closes_at' => '12:00'],
        ['weekday' => 1, 'opens_at' => '13:00', 'closes_at' => '14:00'],
    ], 'periods.2.weekday'],
]);

it('lets staff read the hours but not change them', function () {
    $staff = User::factory()->create();
    $venue = Venue::factory()->withStaff($staff)->create();
    $venue->operatingHours()->create(['weekday' => 2, 'opens_at' => '09:00', 'closes_at' => '17:00']);

    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}/hours")->assertOk()->assertJsonCount(1, 'data');

    $this->actingAs($staff)->putJson("/api/v1/manage/venues/{$venue->id}/hours", ['periods' => []])
        ->assertForbidden();

    expect($venue->operatingHours()->count())->toBe(1);
});
