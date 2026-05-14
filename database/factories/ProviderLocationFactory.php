<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProviderLocation>
 */
class ProviderLocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'location_name' => fake()->company().' Branch',
            'address_line1' => fake()->streetAddress(),
            'address_line2' => fake()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'Philippines',
            'latitude' => fake()->latitude(4.5, 21.5),
            'longitude' => fake()->longitude(116.0, 127.0),
            'service_radius_km' => fake()->numberBetween(5, 30),
            'is_main' => fake()->boolean(25),
            'is_active' => true,
        ];
    }
}
