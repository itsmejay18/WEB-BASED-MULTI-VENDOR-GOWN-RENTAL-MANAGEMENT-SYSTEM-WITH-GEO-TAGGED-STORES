<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemVariant>
 */
class ItemVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'sku' => strtoupper(Str::random(10)),
            'size_label' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL', '2XL']),
            'color' => fake()->safeColorName(),
            'material' => fake()->randomElement(['Cotton', 'Wool', 'Linen', 'Silk', 'Polyester']),
            'chest_cm' => fake()->randomFloat(2, 70, 140),
            'waist_cm' => fake()->randomFloat(2, 55, 130),
            'length_cm' => fake()->randomFloat(2, 80, 180),
            'inseam_cm' => fake()->randomFloat(2, 60, 100),
            'shoulder_cm' => fake()->randomFloat(2, 30, 70),
            'quantity_available' => fake()->numberBetween(1, 10),
            'additional_notes' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
