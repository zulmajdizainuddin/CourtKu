<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\VenueRole;
use App\Exceptions\ApiException;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A user deletes their own account (NFR-07, PDPA; ADR-033). The account is anonymised, not erased:
 * the name, email and phone are wiped and nobody can log in to it again, but its past bookings stay
 * in the venues' records and reports as "Deleted user", so a venue's history and totals still add up.
 *
 * Refused while it would leave someone stuck: an upcoming booking (the venue expects the player),
 * owning a venue (the venue needs an owner), or being the platform admin.
 */
class AccountDeletion
{
    /** The name shown wherever the account appears after it is deleted. */
    public const NAME = 'Deleted user';

    /** The address a deleted account gets: example.com never receives mail, and it frees the real one. */
    public const EMAIL_DOMAIN = 'deleted.example.com';

    public function delete(User $user): void
    {
        $this->ensureCanDelete($user);

        DB::transaction(function () use ($user) {
            $oldEmail = $user->email;

            // Staff memberships go; owners never get here.
            $user->venues()->detach();
            $user->tokens()->delete();
            DB::table('password_reset_tokens')->where('email', $oldEmail)->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();

            $user->forceFill([
                'name' => self::NAME,
                'email' => "deleted-{$user->id}@".self::EMAIL_DOMAIN,
                'phone' => null,
                // A long random password nobody knows, so the account can never be logged in to.
                'password' => Str::random(64),
                'email_verified_at' => null,
                'remember_token' => null,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ])->save();

            $user->delete();
        });
    }

    private function ensureCanDelete(User $user): void
    {
        if ($user->is_platform_admin) {
            throw new ApiException('ACCOUNT_IS_ADMIN', 'The platform admin account can\'t be deleted here.');
        }

        if ($user->venues()->wherePivot('role', VenueRole::Owner->value)->exists()) {
            throw new ApiException('ACCOUNT_OWNS_VENUE', 'You own a venue. Contact CourtKu to close or hand over the venue first.');
        }

        $upcoming = Booking::where('user_id', $user->id)
            ->whereIn('status', BookingStatus::activeValues())
            ->where('ends_at', '>', now())
            ->count();
        if ($upcoming > 0) {
            throw new ApiException(
                'ACCOUNT_HAS_UPCOMING_BOOKINGS',
                $upcoming === 1
                    ? 'You have an upcoming booking. Cancel it, or wait until it is over, then delete your account.'
                    : "You have {$upcoming} upcoming bookings. Cancel them, or wait until they are over, then delete your account.",
                details: ['count' => $upcoming],
            );
        }
    }
}
