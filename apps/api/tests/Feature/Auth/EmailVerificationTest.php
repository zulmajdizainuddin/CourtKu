<?php

use App\Models\User;
use Illuminate\Support\Facades\URL;

$verificationUrl = fn (User $user, int $minutes = 60) => URL::temporarySignedRoute(
    'verification.verify',
    now()->addMinutes($minutes),
    ['id' => $user->id, 'hash' => sha1($user->email)],
);

// Bad links go to the web app's "check your email" page, which offers a new link,
// instead of Laravel's bare 403 page.
$invalidLinkPage = fn () => config('app.frontend_url').'/verify-email?link=invalid';

it('FR-02 marks the email as verified when the link is opened', function () use ($verificationUrl) {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get($verificationUrl($user))->assertRedirect();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('FR-02 rejects a link that was tampered with', function () use ($verificationUrl, $invalidLinkPage) {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get($verificationUrl($user).'x')->assertRedirect($invalidLinkPage());

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('FR-02 rejects an expired link', function () use ($verificationUrl, $invalidLinkPage) {
    $user = User::factory()->unverified()->create();
    $link = $verificationUrl($user, 60);

    $this->travel(61)->minutes();

    $this->actingAs($user)->get($link)->assertRedirect($invalidLinkPage());

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('FR-02 rejects a link for someone else\'s account', function () use ($verificationUrl, $invalidLinkPage) {
    $owner = User::factory()->unverified()->create();
    $intruder = User::factory()->unverified()->create();

    $this->actingAs($intruder)->get($verificationUrl($owner))->assertRedirect($invalidLinkPage());

    expect($owner->fresh()->hasVerifiedEmail())->toBeFalse();
});
