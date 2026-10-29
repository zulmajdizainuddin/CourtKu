<?php

namespace Database\Factories;

use App\Enums\MalaysianState;
use App\Enums\VenueRole;
use App\Enums\VenueStatus;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Fake venues for tests: Venue::factory()->active()->ownedBy($user)->create().
 *
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Arena '.fake()->unique()->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'state' => fake()->randomElement(MalaysianState::cases()),
            'status' => VenueStatus::Pending,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => VenueStatus::Active, 'approved_at' => now()]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => [
            'status' => VenueStatus::Suspended,
            'approved_at' => now()->subMonth(),
            'suspended_at' => now(),
        ]);
    }

    public function ownedBy(User $user): static
    {
        return $this->afterCreating(fn (Venue $venue) => $venue->members()->attach($user, ['role' => VenueRole::Owner]));
    }

    public function withStaff(User $user): static
    {
        return $this->afterCreating(fn (Venue $venue) => $venue->members()->attach($user, ['role' => VenueRole::Staff]));
    }
}
