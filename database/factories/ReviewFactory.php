<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Item;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'user_id' => User::factory(),
            'provider_id' => Provider::factory(),
            'item_id' => Item::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->sentence(),
            'item_condition_rating' => fake()->numberBetween(1, 5),
            'communication_rating' => fake()->numberBetween(1, 5),
            'photos' => [],
            'is_public' => true,
        ];
    }
}
