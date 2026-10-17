<?php

use App\Models\User;
use Illuminate\Support\Facades\URL;

$verificationUrl = fn (User $user) => URL::temporarySignedRoute(
    'verification.verify',
    now()->addMinutes(60),
    ['id' => $user->id, 'hash' => sha1($user->email)],
);

it('FR-02 marks the email as verified when the link is opened', function () use ($verificationUrl) {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get($verificationUrl($user))->assertRedirect();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('FR-02 rejects a link that was tampered with', function () use ($verificationUrl) {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get($verificationUrl($user).'x')->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('FR-02 rejects a link for someone else\'s account', function () use ($verificationUrl) {
    $owner = User::factory()->unverified()->create();
    $intruder = User::factory()->unverified()->create();

    $this->actingAs($intruder)->get($verificationUrl($owner))->assertForbidden();

    expect($owner->fresh()->hasVerifiedEmail())->toBeFalse();
});
