<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Provider>
 */
class ProviderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'provider']),
            'business_name' => fake()->company(),
            'business_registration' => fake()->bothify('REG-#####'),
            'description' => fake()->paragraph(),
            'logo' => fake()->imageUrl(),
            'cover_photo' => fake()->imageUrl(),
            'rating' => fake()->randomFloat(2, 3, 5),
            'total_reviews' => fake()->numberBetween(0, 500),
            'verification_status' => fake()->randomElement(['pending', 'verified', 'rejected']),
            'commission_rate' => fake()->randomFloat(2, 5, 20),
        ];
    }
}
