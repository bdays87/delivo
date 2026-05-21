<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seeds an initial admin user for local development. Change the
     * password before exposing the API to the public internet.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@delivo.local'],
            [
                'name' => 'Delivo Admin',
                'phone' => '+263770000000',
                'password' => 'Admin12345!',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ],
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
