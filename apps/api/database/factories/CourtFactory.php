<?php

namespace Database\Factories;

use App\Models\Court;
use App\Models\Sport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fake courts for tests: Court::factory()->for($venue)->offering('badminton', 'futsal')->create().
 *
 * @extends Factory<Court>
 */
class CourtFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Court '.fake()->unique()->numberBetween(1, 9999),
            'is_indoor' => true,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function offering(string ...$sportSlugs): static
    {
        return $this->afterCreating(
            fn (Court $court) => $court->sports()->attach(Sport::whereIn('slug', $sportSlugs)->pluck('id')),
        );
    }
}
