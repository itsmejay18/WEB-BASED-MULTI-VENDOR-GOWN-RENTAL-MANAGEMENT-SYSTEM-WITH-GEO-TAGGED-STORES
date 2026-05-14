<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemPhoto>
 */
class ItemPhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'variant_id' => null,
            'photo_url' => fake()->imageUrl(1200, 1200, 'fashion'),
            'is_primary' => fake()->boolean(25),
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }
}
