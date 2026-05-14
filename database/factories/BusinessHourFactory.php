<?php

namespace Database\Factories;

use App\Models\ProviderLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BusinessHour>
 */
class BusinessHourFactory extends Factory
{
    public function definition(): array
    {
        return [
            'provider_location_id' => ProviderLocation::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'open_time' => '09:00:00',
            'close_time' => '18:00:00',
            'is_closed' => false,
        ];
    }
}
