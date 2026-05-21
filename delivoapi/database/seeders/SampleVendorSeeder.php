<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class SampleVendorSeeder extends Seeder
{
    /**
     * Seeds one active demo vendor (owner + Vendor row) so SampleProductsSeeder
     * has something to attach products to.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $owner = User::query()->firstOrCreate(
            ['email' => 'demo.vendor@delivo.local'],
            [
                'name' => 'Demo Vendor',
                'phone' => '+263770000111',
                'password' => 'Demo12345!',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ],
        );

        if (! $owner->hasRole('vendor')) {
            $owner->assignRole('vendor');
        }

        Vendor::query()->firstOrCreate(
            ['owner_user_id' => $owner->id],
            [
                'business_name' => 'Delivo Demo Store',
                'slug' => 'delivo-demo-store',
                'support_email' => 'hello@delivo-demo.test',
                'support_phone' => '+263770000111',
                'tin' => 'DEMO-TIN-001',
                'registration_no' => 'DEMO-REG-001',
                'status' => Vendor::STATUS_ACTIVE,
                'approved_at' => now(),
            ],
        );
    }
}
