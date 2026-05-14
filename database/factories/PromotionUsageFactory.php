<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromotionUsage>
 */
class PromotionUsageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'promotion_id' => Promotion::factory(),
            'booking_id' => Booking::factory(),
            'user_id' => User::factory(),
            'discount_amount' => fake()->randomFloat(2, 20, 1000),
        ];
    }
}
