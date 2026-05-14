<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['booking_created', 'booking_status_changed', 'payment_received']),
            'title' => fake()->sentence(4),
            'body' => fake()->sentence(),
            'data' => ['source' => 'factory'],
            'is_read' => fake()->boolean(40),
            'read_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
