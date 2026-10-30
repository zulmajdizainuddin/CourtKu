<?php

use App\Models\User;
use App\Models\Venue;

$settings = fn (array $overrides = []) => array_merge([
    'slot_minutes' => 30, 'min_minutes' => 60, 'max_minutes' => 120, 'booking_window_days' => 30, 'min_notice_minutes' => 0,
], $overrides);

it('starts every venue with sensible booking settings', function () {
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}")
        ->assertJsonPath('data.slot_minutes', 60)
        ->assertJsonPath('data.min_minutes', 60)
        ->assertJsonPath('data.max_minutes', 180)
        ->assertJsonPath('data.booking_window_days', 14)
        // Last-minute bookings are allowed until the start time, e.g. at 7:50 pm for 8:00 pm.
        ->assertJsonPath('data.min_notice_minutes', 0);
});

it('FR-13 lets the owner ask for notice before a booking starts', function (int $minutes) use ($settings) {
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/settings", $settings(['min_notice_minutes' => $minutes]))
        ->assertOk()
        ->assertJsonPath('data.min_notice_minutes', $minutes);
})->with([0, 30, 60, 120, 1440]);

it('FR-13 saves slot length, shortest and longest booking, and how far ahead players can book', function () use ($settings) {
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/settings", $settings())
        ->assertOk()
        ->assertJsonPath('data.slot_minutes', 30)
        ->assertJsonPath('data.max_minutes', 120)
        ->assertJsonPath('data.booking_window_days', 30);
});

it('checks the settings make sense together', function (array $input, string $field) use ($settings) {
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/settings", $settings($input))
        ->assertStatus(422)
        ->assertJsonValidationErrors($field, 'error.details.fields');
})->with([
    '45-minute slots' => [['slot_minutes' => 45], 'slot_minutes'],
    'longest shorter than shortest' => [['min_minutes' => 120, 'max_minutes' => 60], 'max_minutes'],
    '90 minutes with 60-minute slots' => [['slot_minutes' => 60, 'min_minutes' => 90, 'max_minutes' => 180], 'min_minutes'],
    'more than 12 hours' => [['max_minutes' => 750], 'max_minutes'],
    'more than 90 days ahead' => [['booking_window_days' => 91], 'booking_window_days'],
    'no days ahead' => [['booking_window_days' => 0], 'booking_window_days'],
    'notice that is not one of the choices' => [['min_notice_minutes' => 45], 'min_notice_minutes'],
]);

it('lets staff see the settings but not change them', function () use ($settings) {
    $staff = User::factory()->create();
    $venue = Venue::factory()->withStaff($staff)->create();

    $this->actingAs($staff)->putJson("/api/v1/manage/venues/{$venue->id}/settings", $settings())->assertForbidden();

    expect($venue->refresh()->slot_minutes)->toBe(60);
});
