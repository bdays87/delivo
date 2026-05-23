<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Motorbike', 'icon' => 'lucide:bike', 'sort_order' => 1],
            ['name' => 'Sedan', 'icon' => 'lucide:car', 'sort_order' => 2],
            ['name' => 'Pickup', 'icon' => 'lucide:caravan', 'sort_order' => 3],
            ['name' => 'Van', 'icon' => 'lucide:truck', 'sort_order' => 4],
            ['name' => 'Light truck', 'icon' => 'lucide:truck', 'sort_order' => 5],
            ['name' => 'Heavy truck', 'icon' => 'lucide:truck', 'sort_order' => 6],
        ];

        foreach ($types as $t) {
            VehicleType::query()->updateOrCreate(
                ['name' => $t['name']],
                array_merge($t, ['status' => VehicleType::STATUS_ACTIVE]),
            );
        }
    }
}
