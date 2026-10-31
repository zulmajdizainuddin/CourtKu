<?php

namespace App\Models;

use App\Enums\MalaysianState;
use App\Enums\PaymentMode;
use App\Enums\VenueRole;
use App\Enums\VenueStatus;
use App\Exceptions\ApiException;
use Database\Factories\VenueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
 * @property int $slot_minutes 30 or 60 (FR-13)
 * @property int $min_minutes
 * @property int $max_minutes
 * @property int $booking_window_days
 * @property int $min_notice_minutes 0 = players can book right up to the start time
 * @property int $cancel_cutoff_hours players cancel online until this many hours before the start (FR-24)
 * @property int $hold_minutes how long a player has to pay online before the time is freed (FR-16)
 * @property PaymentMode $payment_mode counter, online or both (FR-17)
 * @property VenuePaymentAccount|null $paymentAccount
 */
#[Fillable(['name', 'slug', 'state', 'description', 'address_line', 'city', 'postcode', 'lat', 'lng', 'phone', 'facilities', 'slot_minutes', 'min_minutes', 'max_minutes', 'booking_window_days', 'min_notice_minutes', 'cancel_cutoff_hours', 'hold_minutes', 'payment_mode'])]
class Venue extends Model
{
    /** @use HasFactory<VenueFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'pending',
        // The same defaults as the database columns (FR-13).
        'slot_minutes' => 60,
        'min_minutes' => 60,
        'max_minutes' => 180,
        'booking_window_days' => 14,
        'min_notice_minutes' => 0,
        'cancel_cutoff_hours' => 24,
        'hold_minutes' => 15,
        'payment_mode' => 'counter',
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
            'slot_minutes' => 'integer',
            'min_minutes' => 'integer',
            'max_minutes' => 'integer',
            'booking_window_days' => 'integer',
            'min_notice_minutes' => 'integer',
            'cancel_cutoff_hours' => 'integer',
            'hold_minutes' => 'integer',
            'payment_mode' => PaymentMode::class,
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
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Times courts are closed for repairs or a private event (FR-21).
     *
     * @return HasMany<SlotBlock, $this>
     */
    public function slotBlocks(): HasMany
    {
        return $this->hasMany(SlotBlock::class);
    }

    /**
     * @return HasMany<PriceRule, $this>
     */
    public function priceRules(): HasMany
    {
        return $this->hasMany(PriceRule::class);
    }

    /**
     * @return HasMany<VenueInvite, $this>
     */
    public function invites(): HasMany
    {
        return $this->hasMany(VenueInvite::class);
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
     * The venue's own payment account for online payments (ADR-009), if connected.
     *
     * @return HasOne<VenuePaymentAccount, $this>
     */
    public function paymentAccount(): HasOne
    {
        return $this->hasOne(VenuePaymentAccount::class);
    }

    /**
     * @return HasMany<VenueClosure, $this>
     */
    public function closures(): HasMany
    {
        return $this->hasMany(VenueClosure::class);
    }

    /**
     * Players can pay online only when the owner chose it and the venue's own Stripe account can take
     * payments (ADR-009). If the account stops being ready, players simply pay at the counter.
     */
    public function acceptsOnlinePayment(): bool
    {
        return $this->payment_mode->needsAccount() && (bool) $this->paymentAccount?->isReady();
    }

    /** Pay at the counter: always, unless the owner chose online only and online works. */
    public function acceptsCounterPayment(): bool
    {
        return $this->payment_mode !== PaymentMode::Online || ! $this->acceptsOnlinePayment();
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
