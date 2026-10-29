<?php

use App\Enums\VenueRole;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueInvite;
use App\Notifications\StaffInvite;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

beforeEach(fn () => Notification::fake());

/** An owner (with two-factor) and their venue. */
function staffOwnerAndVenue(): array
{
    $owner = User::factory()->withTwoFactor()->create(['name' => 'Farid']);

    return [$owner, Venue::factory()->ownedBy($owner)->create(['name' => 'Arena Sg Buloh'])];
}

/** Invites the email and returns the secret code from the link in the email. */
function inviteAndGetToken($test, User $owner, Venue $venue, string $email): string
{
    $test->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/staff-invites", ['email' => $email])->assertCreated();

    $token = null;
    Notification::assertSentOnDemand(StaffInvite::class, function (StaffInvite $mail, array $channels, AnonymousNotifiable $to) use ($email, &$token) {
        $token = basename($mail->link());

        return $to->routes['mail'] === $email;
    });

    return $token;
}

it('FR-05 emails an invite link and stores only a hash of its code', function () {
    [$owner, $venue] = staffOwnerAndVenue();

    $token = inviteAndGetToken($this, $owner, $venue, 'aina@example.com');

    $invite = VenueInvite::sole();
    expect(strlen($token))->toBe(40)
        ->and($invite->token_hash)->toBe(hash('sha256', $token))
        ->and($invite->token_hash)->not->toContain($token)
        ->and($invite->role)->toBe(VenueRole::Staff)
        ->and($invite->expires_at->isSameDay(now()->addDays(7)))->toBeTrue();
});

it('sends a fresh link when the same email is invited again; the old link stops working', function () {
    [$owner, $venue] = staffOwnerAndVenue();
    $oldToken = inviteAndGetToken($this, $owner, $venue, 'aina@example.com');

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/staff-invites", ['email' => 'AINA@example.com'])->assertCreated();

    expect(VenueInvite::count())->toBe(1)
        ->and(VenueInvite::findByToken($oldToken))->toBeNull();
});

it('refuses to invite someone who already works here', function () {
    [$owner, $venue] = staffOwnerAndVenue();
    $venue->members()->attach(User::factory()->create(['email' => 'aina@example.com']), ['role' => VenueRole::Staff]);

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/staff-invites", ['email' => 'aina@example.com'])
        ->assertStatus(422)
        ->assertJsonPath('error.details.fields.email.0', 'This person already works at this venue.');

    Notification::assertNothingSent();
});

it('lists the staff and the invites still waiting', function () {
    [$owner, $venue] = staffOwnerAndVenue();
    $venue->members()->attach(User::factory()->create(['name' => 'Aina']), ['role' => VenueRole::Staff]);
    inviteAndGetToken($this, $owner, $venue, 'ben@example.com');

    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/staff")
        ->assertOk()
        ->assertJsonPath('data.members.0.name', 'Farid')
        ->assertJsonPath('data.members.0.role', 'owner')
        ->assertJsonPath('data.members.1.name', 'Aina')
        ->assertJsonPath('data.members.1.role', 'staff')
        ->assertJsonPath('data.invites.0.email', 'ben@example.com');
});

it('FR-05 lets the invited person join as staff with the invited, verified email', function () {
    [$owner, $venue] = staffOwnerAndVenue();
    $token = inviteAndGetToken($this, $owner, $venue, 'aina@example.com');
    $aina = User::factory()->create(['email' => 'aina@example.com']);

    $this->actingAs($aina)->getJson("/api/v1/invites/{$token}")
        ->assertOk()
        ->assertJsonPath('data.venue_name', 'Arena Sg Buloh')
        ->assertJsonPath('data.email_hint', 'a***@example.com')
        ->assertJsonPath('data.for_this_account', true);

    $this->actingAs($aina)->postJson("/api/v1/invites/{$token}/accept")
        ->assertOk()
        ->assertJsonPath('data.role', 'staff');

    expect($venue->roleOf($aina))->toBe(VenueRole::Staff);

    // A link works once.
    $this->actingAs($aina)->postJson("/api/v1/invites/{$token}/accept")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'INVITE_USED');
});

it('lets a player with no venue join as staff', function () {
    [$owner, $venue] = staffOwnerAndVenue();
    $zul = User::factory()->create(['email' => 'zul@example.com']);
    $token = inviteAndGetToken($this, $owner, $venue, 'zul@example.com');

    $this->actingAs($zul)->postJson("/api/v1/invites/{$token}/accept")->assertOk();

    expect($venue->roleOf($zul))->toBe(VenueRole::Staff);
    // Staff only: no owner powers at this venue.
    $this->actingAs($zul)->putJson("/api/v1/manage/venues/{$venue->id}/courts/order", ['court_ids' => []])->assertForbidden();
});

it('lets the owner of one venue be staff at another; each venue keeps its own role', function () {
    [$owner, $venue] = staffOwnerAndVenue();
    $zul = User::factory()->withTwoFactor()->create(['email' => 'zul@example.com']);
    $zulsOwnVenue = Venue::factory()->ownedBy($zul)->create();
    $token = inviteAndGetToken($this, $owner, $venue, 'zul@example.com');

    $this->actingAs($zul)->postJson("/api/v1/invites/{$token}/accept")->assertOk();

    expect($venue->roleOf($zul))->toBe(VenueRole::Staff)
        ->and($zulsOwnVenue->roleOf($zul))->toBe(VenueRole::Owner);
});

it('refuses to invite the venue\'s own owner as staff', function () {
    [$owner, $venue] = staffOwnerAndVenue();

    $this->actingAs($owner)->postJson("/api/v1/manage/venues/{$venue->id}/staff-invites", ['email' => $owner->email])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email', 'error.details.fields');

    expect($venue->roleOf($owner))->toBe(VenueRole::Owner);
    Notification::assertNothingSent();
});

it('is useless to anyone else who gets the link', function () {
    [$owner, $venue] = staffOwnerAndVenue();
    $token = inviteAndGetToken($this, $owner, $venue, 'aina@example.com');
    $someoneElse = User::factory()->create(['email' => 'intruder@example.com']);

    $this->actingAs($someoneElse)->postJson("/api/v1/invites/{$token}/accept")
        ->assertForbidden()
        ->assertJsonPath('error.code', 'INVITE_WRONG_ACCOUNT');

    expect($venue->roleOf($someoneElse))->toBeNull();
});

it('refuses expired, cancelled and made-up links, and unverified accounts', function () {
    [$owner, $venue] = staffOwnerAndVenue();
    $token = inviteAndGetToken($this, $owner, $venue, 'aina@example.com');
    $aina = User::factory()->create(['email' => 'aina@example.com']);

    $this->actingAs(User::factory()->unverified()->create(['email' => 'new@example.com']))
        ->postJson("/api/v1/invites/{$token}/accept")
        ->assertForbidden();

    $this->actingAs($aina)->postJson('/api/v1/invites/'.str_repeat('x', 40).'/accept')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'INVITE_NOT_FOUND');

    $this->travel(8)->days();
    $this->actingAs($aina)->postJson("/api/v1/invites/{$token}/accept")
        ->assertStatus(410)
        ->assertJsonPath('error.code', 'INVITE_EXPIRED');
    $this->travelBack();

    $this->actingAs($owner)->deleteJson("/api/v1/manage/venues/{$venue->id}/staff-invites/".VenueInvite::sole()->id)->assertNoContent();
    $this->actingAs($aina)->postJson("/api/v1/invites/{$token}/accept")->assertNotFound();

    expect($venue->roleOf($aina))->toBeNull();
});

it('removes a staff member, but never the owner', function () {
    [$owner, $venue] = staffOwnerAndVenue();
    $aina = User::factory()->create();
    $venue->members()->attach($aina, ['role' => VenueRole::Staff]);

    $this->actingAs($owner)->deleteJson("/api/v1/manage/venues/{$venue->id}/staff/{$owner->id}")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'CANNOT_REMOVE_OWNER');

    $this->actingAs($owner)->deleteJson("/api/v1/manage/venues/{$venue->id}/staff/{$aina->id}")->assertNoContent();

    expect($venue->roleOf($aina))->toBeNull()
        ->and($venue->roleOf($owner))->toBe(VenueRole::Owner);
    // Removed staff can't open the venue any more.
    $this->actingAs($aina)->getJson("/api/v1/manage/venues/{$venue->id}")->assertNotFound();
});

it('keeps staff management away from staff', function () {
    $staff = User::factory()->create();
    $venue = Venue::factory()->withStaff($staff)->create();

    $this->actingAs($staff)->getJson("/api/v1/manage/venues/{$venue->id}/staff")->assertForbidden();
    $this->actingAs($staff)->postJson("/api/v1/manage/venues/{$venue->id}/staff-invites", ['email' => 'friend@example.com'])
        ->assertForbidden();

    Notification::assertNothingSent();
});
