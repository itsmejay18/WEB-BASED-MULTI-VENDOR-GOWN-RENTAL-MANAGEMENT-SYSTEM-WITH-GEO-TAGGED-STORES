<?php

namespace Database\Seeders;

use App\Models\BusinessHour;
use App\Models\Provider;
use App\Models\ProviderLocation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(10)->create(['role' => 'provider'])->each(function (User $user) {
            $user->assignRole('provider');

            $provider = Provider::factory()->create([
                'user_id' => $user->id,
                'verification_status' => 'verified',
            ]);

            $location = ProviderLocation::factory()->create([
                'provider_id' => $provider->id,
                'is_main' => true,
            ]);

            foreach (range(0, 6) as $day) {
                BusinessHour::factory()->create([
                    'provider_location_id' => $location->id,
                    'day_of_week' => $day,
                    'is_closed' => false,
                ]);
            }
        });
    }
}
