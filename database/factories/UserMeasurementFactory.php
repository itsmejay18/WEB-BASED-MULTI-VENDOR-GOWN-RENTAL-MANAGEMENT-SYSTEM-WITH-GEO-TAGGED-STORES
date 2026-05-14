<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserMeasurement>
 */
class UserMeasurementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'height_cm' => fake()->randomFloat(2, 140, 200),
            'weight_kg' => fake()->randomFloat(2, 40, 120),
            'chest_cm' => fake()->randomFloat(2, 70, 140),
            'waist_cm' => fake()->randomFloat(2, 55, 130),
            'hips_cm' => fake()->randomFloat(2, 70, 150),
            'inseam_cm' => fake()->randomFloat(2, 60, 100),
            'shoulder_width_cm' => fake()->randomFloat(2, 30, 70),
            'dress_size' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL']),
            'pant_size' => fake()->randomElement(['28', '30', '32', '34', '36']),
            'shirt_size' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL']),
            'shoe_size' => fake()->randomElement(['6', '7', '8', '9', '10', '11']),
            'last_updated' => now(),
        ];
    }
}
