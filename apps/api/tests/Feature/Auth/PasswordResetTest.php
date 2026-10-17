<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('FR-02 emails a reset link that points to the web app', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->postJson('/auth/forgot-password', ['email' => $user->email])->assertOk();

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
        $link = $notification->toMail($user)->actionUrl;

        return str_starts_with($link, config('app.frontend_url').'/reset-password?token=');
    });
});

it('FR-02 sets a new password with a valid reset token', function () {
    Notification::fake();
    $user = User::factory()->create();
    $this->postJson('/auth/forgot-password', ['email' => $user->email]);

    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    $this->postJson('/auth/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'Brand-New-Pass-456',
        'password_confirmation' => 'Brand-New-Pass-456',
    ])->assertOk();

    expect(Hash::check('Brand-New-Pass-456', $user->fresh()->password))->toBeTrue();
});
