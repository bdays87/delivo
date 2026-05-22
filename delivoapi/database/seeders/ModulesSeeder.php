<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Submodule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ModulesSeeder extends Seeder
{
    /**
     * Seeds the four admin modules + their submodules + matching Spatie
     * permissions, then grants every one of those permissions to the
     * `admin` role so the seeded admin user sees the full sidebar.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $tree = [
            [
                'name' => 'Marketplace',
                'icon' => 'lucide:store',
                'sort_order' => 1,
                'default_permission' => 'can.access.marketplace',
                'submodules' => [
                    [
                        'name' => 'Vendors',
                        'icon' => 'lucide:building-2',
                        'url' => '/admin/vendors',
                        'default_permission' => 'can.access.vendors',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Categories',
                        'icon' => 'lucide:tag',
                        'url' => '/admin/categories',
                        'default_permission' => 'can.access.categories',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Products',
                        'icon' => 'lucide:package',
                        'url' => '/admin/products',
                        'default_permission' => 'can.access.products',
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Operations',
                'icon' => 'lucide:truck',
                'sort_order' => 2,
                'default_permission' => 'can.access.operations',
                'submodules' => [
                    [
                        'name' => 'Orders',
                        'icon' => 'lucide:shopping-cart',
                        'url' => '/admin/orders',
                        'default_permission' => 'can.access.orders',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Shipping',
                        'icon' => 'lucide:map',
                        'url' => '/admin/shipping',
                        'default_permission' => 'can.access.shipping',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Disputes',
                        'icon' => 'lucide:gavel',
                        'url' => '/admin/disputes',
                        'default_permission' => 'can.access.disputes',
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Finance',
                'icon' => 'lucide:banknote',
                'sort_order' => 3,
                'default_permission' => 'can.access.finance',
                'submodules' => [
                    [
                        'name' => 'Payments',
                        'icon' => 'lucide:credit-card',
                        'url' => '/admin/payments',
                        'default_permission' => 'can.access.payments',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Payouts',
                        'icon' => 'lucide:send',
                        'url' => '/admin/payouts',
                        'default_permission' => 'can.access.payouts',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Exchange rates',
                        'icon' => 'lucide:repeat',
                        'url' => '/admin/exchange-rates',
                        'default_permission' => 'can.access.exchange-rates',
                        'sort_order' => 3,
                    ],
                    [
                        'name' => 'Mobile wallets',
                        'icon' => 'lucide:smartphone',
                        'url' => '/admin/mobile-wallets',
                        'default_permission' => 'can.access.mobile-wallets',
                        'sort_order' => 4,
                    ],
                    [
                        'name' => 'Coverage areas',
                        'icon' => 'lucide:map-pinned',
                        'url' => '/admin/delivery-zones',
                        'default_permission' => 'can.access.delivery-zones',
                        'sort_order' => 5,
                    ],
                ],
            ],
            [
                'name' => 'System',
                'icon' => 'lucide:settings',
                'sort_order' => 4,
                'default_permission' => 'can.access.system',
                'submodules' => [
                    [
                        'name' => 'Users',
                        'icon' => 'lucide:users',
                        'url' => '/admin/users',
                        'default_permission' => 'can.access.users',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Roles',
                        'icon' => 'lucide:shield',
                        'url' => '/admin/roles',
                        'default_permission' => 'can.access.roles',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Modules',
                        'icon' => 'lucide:layout-grid',
                        'url' => '/admin/modules',
                        'default_permission' => 'can.access.modules',
                        'sort_order' => 3,
                    ],
                    [
                        'name' => 'Settings',
                        'icon' => 'lucide:sliders',
                        'url' => '/admin/settings',
                        'default_permission' => 'can.access.settings',
                        'sort_order' => 4,
                    ],
                ],
            ],
        ];

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $grant = [];

        foreach ($tree as $moduleData) {
            $modulePermission = $this->ensurePermission($moduleData['default_permission']);
            $grant[] = $modulePermission->name;

            $module = Module::firstOrCreate(
                ['default_permission' => $moduleData['default_permission']],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $moduleData['name'],
                    'icon' => $moduleData['icon'],
                    'status' => Module::STATUS_ACTIVE,
                    'sort_order' => $moduleData['sort_order'],
                    'default_permission' => $moduleData['default_permission'],
                ],
            );

            foreach ($moduleData['submodules'] as $sub) {
                $subPermission = $this->ensurePermission($sub['default_permission']);
                $grant[] = $subPermission->name;

                $submodule = Submodule::firstOrCreate(
                    ['default_permission' => $sub['default_permission']],
                    [
                        'uuid' => (string) Str::uuid(),
                        'module_id' => $module->id,
                        'name' => $sub['name'],
                        'icon' => $sub['icon'],
                        'url' => $sub['url'],
                        'status' => Submodule::STATUS_ACTIVE,
                        'sort_order' => $sub['sort_order'],
                        'default_permission' => $sub['default_permission'],
                    ],
                );

                // Pivot backfill: every submodule owns its own default permission
                // AND its parent module's default permission. The admin
                // module-view endpoint also self-heals these on read.
                $submodule->permissions()->syncWithoutDetaching([
                    $modulePermission->id,
                    $subPermission->id,
                ]);
            }
        }

        $adminRole->givePermissionTo($grant);

        // Idempotent rename: bring already-seeded submodules in line with the
        // current array values without resetting other admin customisations.
        Submodule::where('default_permission', 'can.access.delivery-zones')
            ->update(['name' => 'Coverage areas']);
    }

    private function ensurePermission(string $name): Permission
    {
        return Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }
}
