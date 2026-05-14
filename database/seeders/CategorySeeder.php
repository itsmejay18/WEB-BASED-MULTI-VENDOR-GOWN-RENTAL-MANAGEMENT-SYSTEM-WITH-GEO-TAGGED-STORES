<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Formal Wear',
            'Traditional',
            'Cocktail Dresses',
            'Wedding',
            'Costumes',
            'Accessories',
        ];

        foreach ($categories as $index => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => "{$name} category",
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }
}
