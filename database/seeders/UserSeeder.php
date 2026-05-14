<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['renter', 'provider', 'admin'] as $role) {
            Role::findOrCreate($role, 'sanctum');
        }

        $admin = User::factory()->create([
            'email' => 'admin@rentfit.test',
            'first_name' => 'System',
            'last_name' => 'Admin',
            'role' => 'admin',
        ]);
        $admin->assignRole('admin');

        User::factory(20)->create(['role' => 'renter'])->each(fn (User $user) => $user->assignRole('renter'));
    }
}
