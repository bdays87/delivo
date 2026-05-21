<?php

namespace Database\Seeders;

use App\Models\MobileWallet;
use Illuminate\Database\Seeder;

class MobileWalletsSeeder extends Seeder
{
    /**
     * Seeds the canonical Zimbabwean mobile wallet list used by vendor
     * payout configuration. Admin can add/archive more via the admin UI.
     */
    public function run(): void
    {
        $rows = [
            ['code' => 'ECOCASH', 'name' => 'Ecocash', 'sort_order' => 1],
            ['code' => 'NETONE', 'name' => 'NetOne (OneMoney)', 'sort_order' => 2],
            ['code' => 'INNBUCKS', 'name' => 'Innbucks', 'sort_order' => 3],
            ['code' => 'OMARI', 'name' => 'Omari', 'sort_order' => 4],
        ];

        foreach ($rows as $row) {
            MobileWallet::firstOrCreate(
                ['code' => $row['code']],
                array_merge($row, ['status' => MobileWallet::STATUS_ACTIVE]),
            );
        }
    }
}
