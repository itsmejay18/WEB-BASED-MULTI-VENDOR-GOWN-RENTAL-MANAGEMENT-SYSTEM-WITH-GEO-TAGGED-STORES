<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Wedding Guest', 'type' => 'occasion'],
            ['name' => 'Prom', 'type' => 'occasion'],
            ['name' => 'Interview', 'type' => 'occasion'],
            ['name' => 'Black Tie', 'type' => 'occasion'],
            ['name' => 'Plus Size', 'type' => 'style'],
            ['name' => 'Vintage', 'type' => 'style'],
            ['name' => 'Designer', 'type' => 'other'],
            ['name' => 'Sustainable', 'type' => 'other'],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(
                ['slug' => Str::slug($tag['name'])],
                ['name' => $tag['name'], 'type' => $tag['type']]
            );
        }
    }
}
