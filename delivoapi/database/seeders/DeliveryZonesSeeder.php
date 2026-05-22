<?php

namespace Database\Seeders;

use App\Models\DeliveryFee;
use App\Models\DeliveryZone;
use App\Models\PlatformSettings;
use Illuminate\Database\Seeder;

class DeliveryZonesSeeder extends Seeder
{
    /**
     * Coverage areas (each with its dispatch hub) + distance-banded delivery
     * fees + platform settings. Idempotent — re-runs upsert hub fields and
     * fee bands.
     */
    public function run(): void
    {
        PlatformSettings::query()->firstOrCreate([], [
            'service_charge_pct' => 2.50,
            'service_charge_min_usd' => 0.50,
        ]);

        // Coverage areas with dispatch hubs. Lat/long are approximate city
        // centres — admins should set the actual hub coordinates in the UI.
        $zones = [
            ['city' => 'Harare', 'hub_name' => 'Delivo Harare Hub', 'hub_address' => 'Avondale Shopping Centre, King George Rd, Harare', 'lat' => -17.7945, 'lng' => 31.0335, 'sort_order' => 1],
            ['city' => 'Chitungwiza', 'hub_name' => 'Delivo Chitungwiza Hub', 'hub_address' => 'Town Centre, Seke Rd, Chitungwiza', 'lat' => -18.0125, 'lng' => 31.0758, 'sort_order' => 2],
            ['city' => 'Bulawayo', 'hub_name' => 'Delivo Bulawayo Hub', 'hub_address' => 'City Centre, 8th Avenue, Bulawayo', 'lat' => -20.1539, 'lng' => 28.5887, 'sort_order' => 3],
            ['city' => 'Mutare', 'hub_name' => 'Delivo Mutare Hub', 'hub_address' => 'Sakubva, Mutare', 'lat' => -18.9707, 'lng' => 32.6709, 'sort_order' => 4],
            ['city' => 'Gweru', 'hub_name' => 'Delivo Gweru Hub', 'hub_address' => 'Main Street, Gweru', 'lat' => -19.4499, 'lng' => 29.8164, 'sort_order' => 5],
            ['city' => 'Masvingo', 'hub_name' => 'Delivo Masvingo Hub', 'hub_address' => 'Robert Mugabe St, Masvingo', 'lat' => -20.0700, 'lng' => 30.8278, 'sort_order' => 6],
            ['city' => 'Kwekwe', 'hub_name' => 'Delivo Kwekwe Hub', 'hub_address' => 'Town Centre, Kwekwe', 'lat' => -18.9282, 'lng' => 29.8149, 'sort_order' => 7],
            ['city' => 'Kadoma', 'hub_name' => 'Delivo Kadoma Hub', 'hub_address' => 'Town Centre, Kadoma', 'lat' => -18.3325, 'lng' => 29.9145, 'sort_order' => 8],
            ['city' => 'Victoria Falls', 'hub_name' => 'Delivo Vic Falls Hub', 'hub_address' => 'Town Centre, Victoria Falls', 'lat' => -17.9243, 'lng' => 25.8572, 'sort_order' => 9],
        ];

        foreach ($zones as $z) {
            DeliveryZone::query()->updateOrCreate(
                ['city' => $z['city']],
                [
                    'hub_name' => $z['hub_name'],
                    'hub_address' => $z['hub_address'],
                    'hub_latitude' => $z['lat'],
                    'hub_longitude' => $z['lng'],
                    'sort_order' => $z['sort_order'],
                    'status' => DeliveryZone::STATUS_ACTIVE,
                ],
            );
        }

        // Distance-banded delivery fees. Inclusive ranges; the top band has
        // max_km = null for "and up".
        $bands = [
            ['min_km' => 0, 'max_km' => 50, 'fee_usd' => 5.00, 'sort_order' => 1],
            ['min_km' => 51, 'max_km' => 200, 'fee_usd' => 7.50, 'sort_order' => 2],
            ['min_km' => 201, 'max_km' => 400, 'fee_usd' => 9.00, 'sort_order' => 3],
            ['min_km' => 401, 'max_km' => 700, 'fee_usd' => 12.00, 'sort_order' => 4],
            ['min_km' => 701, 'max_km' => null, 'fee_usd' => 15.00, 'sort_order' => 5],
        ];
        foreach ($bands as $b) {
            DeliveryFee::query()->updateOrCreate(
                ['min_km' => $b['min_km']],
                array_merge($b, ['status' => DeliveryFee::STATUS_ACTIVE]),
            );
        }
    }
}
