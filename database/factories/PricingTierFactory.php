<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingTier>
 */
class PricingTierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'duration_days' => fake()->randomElement([1, 3, 5, 7, 14]),
            'price' => fake()->randomFloat(2, 200, 15000),
            'is_active' => true,
        ];
    }
}
