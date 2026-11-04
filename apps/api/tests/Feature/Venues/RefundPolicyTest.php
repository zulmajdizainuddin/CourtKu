<?php

use App\Models\User;
use App\Models\Venue;

/*
| FR-40: players see the venue's cancellation and refund policy before they pay. The rule comes from
| the cancel cutoff; the owner may add a short note of their own (ADR-032).
*/

$refundSettings = fn (array $overrides = []) => array_merge([
    'slot_minutes' => 60, 'min_minutes' => 60, 'max_minutes' => 180, 'booking_window_days' => 14, 'min_notice_minutes' => 0,
    'cancel_cutoff_hours' => 12,
], $overrides);

it('FR-40 lets the owner add a refund note, and shows it with the cutoff on the public page', function () use ($refundSettings) {
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->active()->ownedBy($owner)->create(['slug' => 'note-arena']);

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/settings", $refundSettings(['refund_note' => '  Rained out? We move your booking.  ']))
        ->assertOk()
        ->assertJsonPath('data.refund_note', 'Rained out? We move your booking.');

    $this->getJson('/api/v1/venues/note-arena')
        ->assertOk()
        ->assertJsonPath('data.booking.cancel_cutoff_hours', 12)
        ->assertJsonPath('data.booking.refund_note', 'Rained out? We move your booking.');
});

it('FR-40 clears the note when it is left empty, and keeps it when the field is not sent', function () use ($refundSettings) {
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->ownedBy($owner)->create(['refund_note' => 'Old note']);

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/settings", $refundSettings())
        ->assertOk()
        ->assertJsonPath('data.refund_note', 'Old note');

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/settings", $refundSettings(['refund_note' => '']))
        ->assertOk()
        ->assertJsonPath('data.refund_note', null);
});

it('FR-40 keeps the note short', function () use ($refundSettings) {
    $owner = User::factory()->withTwoFactor()->create();
    $venue = Venue::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)->putJson("/api/v1/manage/venues/{$venue->id}/settings", $refundSettings(['refund_note' => str_repeat('a', 301)]))
        ->assertUnprocessable()
        ->assertJsonPath('error.details.fields.refund_note.0', 'Keep the note to 300 characters.');
});

it('FR-40 lets only owners change the note', function () use ($refundSettings) {
    $staff = User::factory()->create();
    $venue = Venue::factory()->ownedBy(User::factory()->withTwoFactor()->create())->withStaff($staff)->create();

    $this->actingAs($staff)->putJson("/api/v1/manage/venues/{$venue->id}/settings", $refundSettings(['refund_note' => 'No refunds']))
        ->assertForbidden();
    expect($venue->fresh()->refund_note)->toBeNull();
});
