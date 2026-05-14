<?php

namespace Database\Seeders;

use App\Models\AvailabilityCalendar;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemPhoto;
use App\Models\ItemVariant;
use App\Models\PricingTier;
use App\Models\Provider;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $providers = Provider::all();
        $categories = Category::all();
        $tagIds = Tag::pluck('id');

        $providers->each(function (Provider $provider) use ($categories, $tagIds) {
            Item::factory(8)->create([
                'provider_id' => $provider->id,
                'category_id' => $categories->random()->id,
            ])->each(function (Item $item) use ($tagIds) {
                $item->tags()->sync($tagIds->random(rand(1, min(3, $tagIds->count())))->values());

                $variants = ItemVariant::factory(rand(1, 4))->create(['item_id' => $item->id]);

                PricingTier::factory()->create([
                    'item_id' => $item->id,
                    'duration_days' => 3,
                    'price' => rand(500, 5000),
                ]);
                PricingTier::factory()->create([
                    'item_id' => $item->id,
                    'duration_days' => 7,
                    'price' => rand(1500, 10000),
                ]);

                ItemPhoto::factory(rand(2, 4))->create(['item_id' => $item->id]);

                $primarySet = false;
                foreach ($variants as $variant) {
                    $startDate = Carbon::today();

                    for ($offset = 0; $offset < 30; $offset++) {
                        AvailabilityCalendar::create([
                            'variant_id' => $variant->id,
                            'available_date' => $startDate->copy()->addDays($offset)->format('Y-m-d'),
                            'is_available' => (bool) random_int(0, 100) < 80,
                            'notes' => fake()->optional()->sentence(),
                        ]);
                    }

                    $photo = ItemPhoto::factory()->create([
                        'item_id' => $item->id,
                        'variant_id' => $variant->id,
                        'is_primary' => ! $primarySet,
                    ]);

                    $primarySet = $primarySet || $photo->is_primary;
                }
            });
        });
    }
}
