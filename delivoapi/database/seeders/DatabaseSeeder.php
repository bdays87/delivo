<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            MobileWalletsSeeder::class,
            CategoriesSeeder::class,
            ModulesSeeder::class,
            AdminUserSeeder::class,
            DeliveryZonesSeeder::class,
            VehicleTypesSeeder::class,
            SampleVendorSeeder::class,
            SampleProductsSeeder::class,
        ]);
    }
}
