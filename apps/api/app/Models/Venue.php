<?php

namespace App\Models;

use App\Enums\MalaysianState;
use App\Enums\VenueRole;
use App\Enums\VenueStatus;
use App\Exceptions\ApiException;
use Database\Factories\VenueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A sports venue: one tenant of CourtKu (ADR-002).
 * Everything that belongs to a venue (courts, hours, bookings) carries its venue_id.
 *
 * status, approved_at and is_demo are deliberately not fillable: only approve(),
 * suspend() and the seeder change them, never a request from an owner.
 *
 * @property VenueStatus $status
 * @property MalaysianState $state
 * @property list<string>|null $facilities
 * @property Carbon|null $approved_at
 * @property Carbon|null $suspended_at
 */
#[Fillable(['name', 'slug', 'state', 'description', 'address_line', 'city', 'postcode', 'lat', 'lng', 'phone', 'facilities'])]
class Venue extends Model
{
    /** @use HasFactory<VenueFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'status' => VenueStatus::class,
            'state' => MalaysianState::class,
            'facilities' => 'array',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'is_demo' => 'boolean',
            'approved_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    /**
     * Everyone who works here (owners and staff); the role is on ->pivot->role.
     *
     * @return BelongsToMany<User, $this, VenueMember>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'venue_members')
            ->using(VenueMember::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<User, $this, VenueMember>
     */
    public function owners(): BelongsToMany
    {
        return $this->members()->wherePivot('role', VenueRole::Owner->value);
    }

    /**
     * @return HasMany<Court, $this>
     */
    public function courts(): HasMany
    {
        return $this->hasMany(Court::class);
    }

    /**
     * @return HasMany<VenuePhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(VenuePhoto::class);
    }

    /**
     * @return HasMany<OperatingHour, $this>
     */
    public function operatingHours(): HasMany
    {
        return $this->hasMany(OperatingHour::class);
    }

    /**
     * @return HasMany<VenueClosure, $this>
     */
    public function closures(): HasMany
    {
        return $this->hasMany(VenueClosure::class);
    }

    /** The user's role here, or null if they don't work here. */
    public function roleOf(User $user): ?VenueRole
    {
        return VenueMember::query()
            ->where('venue_id', $this->id)
            ->where('user_id', $user->id)
            ->first()
            ?->role;
    }

    /** Admin: make the venue public (FR-04), or reinstate a suspended one. */
    public function approve(): void
    {
        if (! $this->status->canBeApproved()) {
            throw new ApiException('INVALID_STATUS_CHANGE', 'This venue is already active.');
        }

        $this->forceFill([
            'status' => VenueStatus::Active,
            'approved_at' => $this->approved_at ?? now(),
            'suspended_at' => null,
        ])->save();
    }

    /** Admin: hide the venue from the public, or turn down a pending one. */
    public function suspend(): void
    {
        if (! $this->status->canBeSuspended()) {
            throw new ApiException('INVALID_STATUS_CHANGE', 'This venue is already suspended.');
        }

        $this->forceFill([
            'status' => VenueStatus::Suspended,
            'suspended_at' => now(),
        ])->save();
    }
}
