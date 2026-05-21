<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Seeds top-level marketplace categories aligned with the storefront mock.
     */
    public function run(): void
    {
        $rows = [
            [
                'name' => 'Groceries',
                'slug' => 'groceries',
                'emoji' => '🛒',
                'tint' => 'from-emerald-100 to-emerald-50',
                'icon' => 'lucide:shopping-basket',
                'sort_order' => 1,
            ],
            [
                'name' => 'Fashion',
                'slug' => 'fashion',
                'emoji' => '👜',
                'tint' => 'from-rose-100 to-rose-50',
                'icon' => 'lucide:shirt',
                'sort_order' => 2,
            ],
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'emoji' => '🎧',
                'tint' => 'from-sky-100 to-sky-50',
                'icon' => 'lucide:headphones',
                'sort_order' => 3,
            ],
            [
                'name' => 'Home & Living',
                'slug' => 'home-living',
                'emoji' => '🛋️',
                'tint' => 'from-amber-100 to-amber-50',
                'icon' => 'lucide:sofa',
                'sort_order' => 4,
            ],
            [
                'name' => 'Beauty',
                'slug' => 'beauty',
                'emoji' => '💄',
                'tint' => 'from-fuchsia-100 to-fuchsia-50',
                'icon' => 'lucide:sparkles',
                'sort_order' => 5,
            ],
            [
                'name' => 'Sports',
                'slug' => 'sports',
                'emoji' => '⚽',
                'tint' => 'from-lime-100 to-lime-50',
                'icon' => 'lucide:dumbbell',
                'sort_order' => 6,
            ],
            [
                'name' => 'Books',
                'slug' => 'books',
                'emoji' => '📚',
                'tint' => 'from-orange-100 to-orange-50',
                'icon' => 'lucide:book-open',
                'sort_order' => 7,
            ],
            [
                'name' => 'Toys',
                'slug' => 'toys',
                'emoji' => '🧸',
                'tint' => 'from-violet-100 to-violet-50',
                'icon' => 'lucide:gamepad-2',
                'sort_order' => 8,
            ],
        ];

        foreach ($rows as $row) {
            Category::firstOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, ['status' => Category::STATUS_ACTIVE]),
            );
        }
    }
}
