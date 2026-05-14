<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'user_id' => User::factory(),
            'payment_intent_id' => 'pi_'.Str::uuid()->toString(),
            'amount' => fake()->randomFloat(2, 500, 25000),
            'currency' => 'PHP',
            'payment_method' => fake()->randomElement(['card', 'gcash', 'cash', 'bank_transfer']),
            'status' => fake()->randomElement(['pending', 'succeeded', 'failed', 'refunded']),
            'transaction_id' => 'txn_'.Str::uuid()->toString(),
            'refund_amount' => 0,
            'refund_reason' => null,
            'metadata' => ['source' => 'factory'],
        ];
    }
}
