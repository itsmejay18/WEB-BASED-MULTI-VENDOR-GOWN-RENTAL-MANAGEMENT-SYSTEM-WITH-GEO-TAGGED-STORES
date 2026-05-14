<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    public function definition(): array
    {
        $validFrom = now()->subDays(fake()->numberBetween(0, 2));
        $validTo = now()->addDays(fake()->numberBetween(7, 30));

        return [
            'provider_id' => Provider::factory(),
            'code' => 'PROMO-'.Str::upper(Str::random(6)),
            'description' => fake()->sentence(),
            'discount_type' => fake()->randomElement(['percentage', 'fixed']),
            'discount_value' => fake()->randomFloat(2, 5, 50),
            'min_booking_amount' => fake()->randomFloat(2, 200, 1000),
            'max_discount_amount' => fake()->randomFloat(2, 100, 1500),
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'usage_limit' => fake()->numberBetween(10, 500),
            'used_count' => fake()->numberBetween(0, 5),
            'is_active' => true,
        ];
    }
}
