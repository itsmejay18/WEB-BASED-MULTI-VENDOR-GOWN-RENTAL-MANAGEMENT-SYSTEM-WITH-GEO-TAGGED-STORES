<?php

namespace Database\Factories;

use App\Models\ItemVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AvailabilityCalendar>
 */
class AvailabilityCalendarFactory extends Factory
{
    public function definition(): array
    {
        return [
            'variant_id' => ItemVariant::factory(),
            'available_date' => fake()->dateTimeBetween('today', '+30 days')->format('Y-m-d'),
            'is_available' => fake()->boolean(80),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
