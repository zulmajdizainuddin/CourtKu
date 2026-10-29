<?php

namespace App\Models;

use App\Enums\VenueRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

// is_platform_admin is deliberately not fillable, so nobody can make
// themselves an admin by adding it to a registration request.
#[Fillable(['name', 'email', 'phone', 'password'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
        ];
    }

    /**
     * The venues this user works at; their role at each is on ->pivot->role.
     *
     * @return BelongsToMany<Venue, $this, VenueMember>
     */
    public function venues(): BelongsToMany
    {
        return $this->belongsToMany(Venue::class, 'venue_members')
            ->using(VenueMember::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Owners and the platform admin must use two-factor login; staff and players may.
     * Owning any venue counts, even one still waiting for approval.
     */
    public function requiresTwoFactor(): bool
    {
        return $this->is_platform_admin
            || $this->venues()->wherePivot('role', VenueRole::Owner->value)->exists();
    }
}
