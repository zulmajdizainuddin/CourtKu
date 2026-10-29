<?php

namespace App\Models;

use App\Enums\VenueRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * An invitation to work at a venue (FR-05, ADR-019). The email holds a link with a secret
 * code; only its SHA-256 hash is stored, so the database alone can never be used to join.
 *
 * @property VenueRole $role
 * @property Carbon $expires_at
 * @property Carbon|null $accepted_at
 */
#[Fillable(['email', 'role', 'token_hash', 'invited_by', 'expires_at'])]
class VenueInvite extends Model
{
    /** How long an invite link works. */
    public const DAYS_VALID = 7;

    protected function casts(): array
    {
        return [
            'role' => VenueRole::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Venue, $this>
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** A new secret code for the link: 40 random letters and digits. */
    public static function newToken(): string
    {
        return Str::random(40);
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /** The invite a link's code belongs to, or null. */
    public static function findByToken(string $token): ?self
    {
        return self::where('token_hash', self::hashToken($token))->first();
    }

    /**
     * Not accepted and not expired.
     *
     * @param  Builder<self>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->whereNull('accepted_at')->where('expires_at', '>', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
