<?php

use App\Enums\BookingSource;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Notifications\BookingReminder;
use App\Services\AccountDeletion;
use App\Services\BookingService;
use Carbon\CarbonImmutable;

/*
| NFR-07 (PDPA): a user deletes their own account. It is anonymised, past bookings stay with the venue
| as "Deleted user" (ADR-033). Now is Monday 2 Nov 2026, 10:00 in Malaysia.
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
});

function deletionVenue(?User $owner = null): Venue
{
    $factory = Venue::factory()->active();
    $venue = ($owner ? $factory->ownedBy($owner) : $factory)->create();
    foreach (range(0, 6) as $weekday) {
        $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '08:00', 'closes_at' => '23:00', 'closes_next_day' => false]);
    }
    Court::factory()->for($venue)->offering('badminton')->create(['name' => 'Court 1', 'base_price_sen' => 2000]);

    return $venue;
}

function deletionBooking(Venue $venue, User $player, string $startsAt): Booking
{
    $service = new BookingService;

    return $service->store(
        $service->prepare($venue->courts()->firstOrFail(), CarbonImmutable::parse($startsAt), 60),
        BookingSource::Online,
        player: $player,
        createdBy: $player,
    );
}

it('NFR-07 deletes the account: personal details wiped, logged out, and it can never log in again', function () {
    $player = User::factory()->create(['name' => 'Aina Rahman', 'email' => 'aina@example.com', 'phone' => '+60123456789', 'password' => 'Secret-Pass-2026']);

    $this->actingAs($player)->deleteJson('/api/v1/me', ['password' => 'Secret-Pass-2026'])
        ->assertNoContent();

    $deleted = User::withTrashed()->findOrFail($player->id);
    expect($deleted->trashed())->toBeTrue()
        ->and($deleted->name)->toBe(AccountDeletion::NAME)
        ->and($deleted->email)->toBe("deleted-{$player->id}@deleted.example.com")
        ->and($deleted->phone)->toBeNull()
        ->and($deleted->email_verified_at)->toBeNull();

    $this->app['auth']->forgetGuards();
    $this->getJson('/api/v1/me')->assertUnauthorized();
    $this->postJson('/auth/login', ['email' => 'aina@example.com', 'password' => 'Secret-Pass-2026'])->assertUnprocessable();
});

it('NFR-07 frees the email, so the person can sign up again later', function () {
    $player = User::factory()->create(['email' => 'again@example.com', 'password' => 'Secret-Pass-2026']);
    $this->actingAs($player)->deleteJson('/api/v1/me', ['password' => 'Secret-Pass-2026'])->assertNoContent();
    $this->app['auth']->forgetGuards();

    $this->postJson('/auth/register', [
        'name' => 'Again', 'email' => 'again@example.com', 'phone' => '012-345 6789',
        'password' => 'Another-Pass-2026', 'password_confirmation' => 'Another-Pass-2026',
    ])->assertCreated();
});

it('NFR-07 asks for the password again', function () {
    $player = User::factory()->create(['password' => 'Secret-Pass-2026']);

    $this->actingAs($player)->deleteJson('/api/v1/me', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password', 'error.details.fields');
    $this->actingAs($player)->deleteJson('/api/v1/me', ['password' => 'wrong'])
        ->assertUnprocessable()
        ->assertJsonPath('error.details.fields.password.0', 'That password is not right.');

    expect($player->fresh()->trashed())->toBeFalse();
});

it('NFR-07 keeps past bookings for the venue, shown as "Deleted user"', function () {
    $owner = User::factory()->withTwoFactor()->create();
    $venue = deletionVenue($owner);
    $player = User::factory()->create(['name' => 'Aina Rahman', 'phone' => '+60123456789', 'password' => 'Secret-Pass-2026']);
    $booking = deletionBooking($venue, $player, '2026-11-02T11:00:00+08:00');

    $this->travelTo('2026-11-02 04:30:00'); // 12:30 pm: the 11 am-12 pm game is over
    $this->actingAs($player)->deleteJson('/api/v1/me', ['password' => 'Secret-Pass-2026'])->assertNoContent();

    expect(Booking::find($booking->id)->user_id)->toBe($player->id);
    $this->app['auth']->forgetGuards();
    $this->actingAs($owner)->getJson("/api/v1/manage/venues/{$venue->id}/schedule?date=2026-11-02")
        ->assertOk()
        ->assertJsonPath('data.bookings.0.customer.name', AccountDeletion::NAME)
        ->assertJsonPath('data.bookings.0.customer.phone', null);
});

it('NFR-07 refuses while the player has an upcoming booking', function () {
    $venue = deletionVenue();
    $player = User::factory()->create(['password' => 'Secret-Pass-2026']);
    deletionBooking($venue, $player, '2026-11-03T18:00:00+08:00');

    $this->actingAs($player)->deleteJson('/api/v1/me', ['password' => 'Secret-Pass-2026'])
        ->assertConflict()
        ->assertJsonPath('error.code', 'ACCOUNT_HAS_UPCOMING_BOOKINGS')
        ->assertJsonPath('error.details.count', 1);

    expect($player->fresh()->trashed())->toBeFalse();
});

it('NFR-07 lets a player with only cancelled bookings delete', function () {
    $venue = deletionVenue();
    $player = User::factory()->create(['password' => 'Secret-Pass-2026']);
    $booking = deletionBooking($venue, $player, '2026-11-03T18:00:00+08:00');
    $this->actingAs($player)->postJson("/api/v1/bookings/{$booking->code}/cancel")->assertOk();

    $this->actingAs($player)->deleteJson('/api/v1/me', ['password' => 'Secret-Pass-2026'])->assertNoContent();
});

it('NFR-07 refuses for a venue owner and the platform admin', function () {
    $owner = User::factory()->withTwoFactor()->create(['password' => 'Secret-Pass-2026']);
    Venue::factory()->ownedBy($owner)->create();
    $this->actingAs($owner)->deleteJson('/api/v1/me', ['password' => 'Secret-Pass-2026'])
        ->assertConflict()
        ->assertJsonPath('error.code', 'ACCOUNT_OWNS_VENUE');

    $admin = User::factory()->withTwoFactor()->create(['password' => 'Secret-Pass-2026']);
    $admin->forceFill(['is_platform_admin' => true])->save();
    $this->actingAs($admin)->deleteJson('/api/v1/me', ['password' => 'Secret-Pass-2026'])
        ->assertConflict()
        ->assertJsonPath('error.code', 'ACCOUNT_IS_ADMIN');
});

it('NFR-07 removes a staff member from their venues', function () {
    $staff = User::factory()->create(['password' => 'Secret-Pass-2026']);
    $venue = Venue::factory()->ownedBy(User::factory()->withTwoFactor()->create())->withStaff($staff)->create();

    $this->actingAs($staff)->deleteJson('/api/v1/me', ['password' => 'Secret-Pass-2026'])->assertNoContent();

    expect($venue->members()->whereKey($staff->id)->exists())->toBeFalse();
});

it('NFR-07 never emails a deleted account', function () {
    $venue = deletionVenue();
    $player = User::factory()->create();
    $booking = deletionBooking($venue, $player, '2026-11-03T18:00:00+08:00');
    $sent = fn () => app('mailer')->getSymfonyTransport()->messages()->count();
    $before = $sent(); // the booking's own confirmation email

    // Sent for real through the notification system, so the check in AppServiceProvider runs.
    $player->notify(new BookingReminder($booking));
    expect($sent())->toBe($before + 1);

    $player->delete();
    $player->notify(new BookingReminder($booking));
    expect($sent())->toBe($before + 1);
});
