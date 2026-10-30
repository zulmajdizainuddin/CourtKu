<?php

namespace Database\Factories;

use App\Models\PriceRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fake price rules for tests: PriceRule::factory()->for($venue)->create(['price_sen' => 3500]).
 * By default: every court, Monday to Friday, 6:00 pm to midnight, RM 30.00 an hour.
 *
 * @extends Factory<PriceRule>
 */
class PriceRuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'court_id' => null,
            'name' => 'Weekday peak',
            'weekdays' => [1, 2, 3, 4, 5],
            'starts_at' => '18:00',
            'ends_at' => '00:00',
            'ends_next_day' => true,
            'price_sen' => 3000,
        ];
    }
}
