<?php

use App\Models\User;

it('makes an existing account a platform admin', function () {
    $user = User::factory()->create(['email' => 'boss@example.com']);

    $this->artisan('courtku:make-admin', ['email' => 'Boss@Example.com'])->assertSuccessful();

    expect($user->refresh()->is_platform_admin)->toBeTrue();
});

it('fails for an email nobody has registered', function () {
    $this->artisan('courtku:make-admin', ['email' => 'nobody@example.com'])->assertFailed();
});
