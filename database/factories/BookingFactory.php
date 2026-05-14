<?php

namespace Database\Factories;

use App\Models\ItemVariant;
use App\Models\Provider;
use App\Models\ProviderLocation;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('today', '+10 days');
        $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(1, 5).' days');

        return [
            'booking_number' => 'BK-'.now()->format('Ym').'-'.strtoupper(Str::random(6)),
            'user_id' => User::factory(),
            'provider_id' => Provider::factory(),
            'variant_id' => ItemVariant::factory(),
            'pickup_location_id' => ProviderLocation::factory(),
            'delivery_address_id' => UserAddress::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'rental_price' => fake()->randomFloat(2, 200, 20000),
            'security_deposit' => fake()->randomFloat(2, 0, 5000),
            'delivery_fee' => fake()->randomFloat(2, 0, 500),
            'platform_fee' => fake()->randomFloat(2, 0, 1000),
            'total_amount' => fake()->randomFloat(2, 500, 30000),
            'status' => fake()->randomElement([
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'ready_for_pickup',
                'picked_up',
                'returned',
                'completed',
                'disputed',
            ]),
            'payment_status' => fake()->randomElement(['pending', 'paid', 'refunded', 'partially_refunded']),
            'payment_method' => fake()->randomElement(['card', 'gcash', 'cash', 'bank_transfer']),
            'special_requests' => fake()->optional()->sentence(),
            'rejection_reason' => null,
            'cancelled_by' => null,
        ];
    }
}
