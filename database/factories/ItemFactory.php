<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'provider_id' => Provider::factory(),
            'category_id' => Category::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraph(),
            'brand' => fake()->company(),
            'designer' => fake()->name(),
            'condition_rating' => fake()->randomElement(['new', 'like_new', 'good', 'fair']),
            'cleaning_policy' => fake()->sentence(),
            'security_deposit' => fake()->randomFloat(2, 0, 5000),
            'late_fee_per_day' => fake()->randomFloat(2, 0, 500),
            'total_rentals' => fake()->numberBetween(0, 300),
            'is_active' => true,
            'requires_approval' => fake()->boolean(20),
        ];
    }
}
