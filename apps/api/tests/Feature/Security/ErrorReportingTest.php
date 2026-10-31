<?php

use App\Exceptions\ApiException;
use App\Models\User;
use App\Payments\PaymentGatewayException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Validation\ValidationException;

/*
| Error alerts (docs/deploy.md): unexpected errors go to Sentry; the API saying "no" on purpose doesn't.
*/

it('reports unexpected errors and Stripe not answering, but not business rules or bad input', function () {
    $handler = app(ExceptionHandler::class);

    expect($handler->shouldReport(new RuntimeException('Something broke')))->toBeTrue()
        ->and($handler->shouldReport(new PaymentGatewayException))->toBeTrue()
        ->and($handler->shouldReport(new ApiException('SLOT_TAKEN', 'Someone has just booked this time.')))->toBeFalse()
        ->and($handler->shouldReport(ValidationException::withMessages(['email' => 'Required.'])))->toBeFalse();
});

it('lets the admin send a test error, which is reported', function () {
    Exceptions::fake();
    $admin = User::factory()->withTwoFactor()->create()->forceFill(['is_platform_admin' => true]);

    $this->actingAs($admin)->postJson('/api/v1/admin/test-error')->assertStatus(500);

    Exceptions::assertReported(fn (RuntimeException $e) => str_contains($e->getMessage(), 'Test error from the CourtKu admin page'));
});

it('refuses the test error to everyone else', function () {
    $this->postJson('/api/v1/admin/test-error')->assertUnauthorized();
    $this->actingAs(User::factory()->withTwoFactor()->create())->postJson('/api/v1/admin/test-error')->assertForbidden();
});

it('never sends request bodies, IP addresses or user details to Sentry, and only errors', function () {
    expect(config('sentry.send_default_pii'))->toBeFalse()
        ->and(config('sentry.max_request_body_size'))->toBe('never')
        ->and(config('sentry.traces_sample_rate'))->toBeNull()
        ->and(config('sentry.enable_metrics'))->toBeFalse();
});
