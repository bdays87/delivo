<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use App\Models\PlatformSettings;
use Illuminate\Database\Seeder;

class DeliveryZonesSeeder extends Seeder
{
    /**
     * Default Zimbabwean delivery zones + platform settings row. Idempotent.
     */
    public function run(): void
    {
        PlatformSettings::query()->firstOrCreate([], [
            'service_charge_pct' => 2.50,
            'service_charge_min_usd' => 0.50,
            'default_delivery_fee_usd' => 10.00,
        ]);

        $zones = [
            ['city' => 'Harare', 'fee_usd' => 5.00, 'sort_order' => 1],
            ['city' => 'Chitungwiza', 'fee_usd' => 6.00, 'sort_order' => 2],
            ['city' => 'Bulawayo', 'fee_usd' => 7.00, 'sort_order' => 3],
            ['city' => 'Mutare', 'fee_usd' => 8.00, 'sort_order' => 4],
            ['city' => 'Gweru', 'fee_usd' => 8.00, 'sort_order' => 5],
            ['city' => 'Masvingo', 'fee_usd' => 9.00, 'sort_order' => 6],
            ['city' => 'Kwekwe', 'fee_usd' => 8.50, 'sort_order' => 7],
            ['city' => 'Kadoma', 'fee_usd' => 7.50, 'sort_order' => 8],
            ['city' => 'Victoria Falls', 'fee_usd' => 12.00, 'sort_order' => 9],
        ];

        foreach ($zones as $z) {
            DeliveryZone::query()->firstOrCreate(
                ['city' => $z['city']],
                array_merge($z, ['status' => DeliveryZone::STATUS_ACTIVE]),
            );
        }
    }
}
