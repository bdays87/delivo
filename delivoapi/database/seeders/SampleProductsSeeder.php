<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPriceTier;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SampleProductsSeeder extends Seeder
{
    /**
     * Per-category color palette used by the GD image generator. Tuple is
     * [topHex, bottomHex, accentHex] for the vertical gradient + watermark.
     */
    private const PALETTE = [
        'groceries' => ['#10b981', '#d1fae5', '#065f46'],
        'fashion' => ['#f43f5e', '#ffe4e6', '#9f1239'],
        'electronics' => ['#0ea5e9', '#e0f2fe', '#075985'],
        'home-living' => ['#f59e0b', '#fef3c7', '#92400e'],
        'beauty' => ['#d946ef', '#fae8ff', '#86198f'],
        'sports' => ['#84cc16', '#ecfccb', '#3f6212'],
        'books' => ['#f97316', '#ffedd5', '#9a3412'],
        'toys' => ['#8b5cf6', '#ede9fe', '#5b21b6'],
    ];

    public function run(): void
    {
        $vendor = Vendor::where('slug', 'delivo-demo-store')->first();
        if ($vendor === null) {
            $this->command?->warn('SampleProductsSeeder skipped: run SampleVendorSeeder first.');

            return;
        }

        $categories = Category::all()->keyBy('slug');
        $now = now();

        foreach ($this->products() as $row) {
            $category = $categories->get($row['category']);
            if ($category === null) {
                continue;
            }

            $slug = 'sample-'.$row['slug'];

            /** @var Product $product */
            $product = Product::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'vendor_id' => $vendor->id,
                    'category_id' => $category->id,
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'sku' => $row['sku'] ?? null,
                    'weight_kg' => $row['weight_kg'] ?? null,
                    'status' => Product::STATUS_ACTIVE,
                    'submitted_at' => $now,
                    'approved_at' => $now,
                ],
            );

            // Idempotent: nuke and re-create tiers/variants so re-runs reflect
            // any seeder data changes.
            $product->priceTiers()->delete();
            foreach ($row['price_tiers'] as $tier) {
                ProductPriceTier::query()->create([
                    'product_id' => $product->id,
                    'min_qty' => $tier['min_qty'],
                    'unit_price' => $tier['unit_price'],
                ]);
            }

            $product->variants()->delete();
            foreach ($row['variants'] as $variant) {
                ProductVariant::query()->create([
                    'product_id' => $product->id,
                    'color' => $variant['color'] ?? null,
                    'stock_quantity' => $variant['stock_quantity'],
                    'sku' => $variant['sku'] ?? null,
                ]);
            }

            // (Re)generate images. Wipe prior demo images first so re-runs don't pile up.
            foreach ($product->images as $existing) {
                Storage::disk($existing->disk)->delete($existing->path);
                $existing->delete();
            }

            $imageCount = $row['image_count'] ?? 2;
            for ($i = 0; $i < $imageCount; $i++) {
                $relPath = "products/{$product->id}/sample-".($i + 1).'.png';
                $this->generateImage(
                    $row['category'],
                    $row['name'],
                    $category->name,
                    $i,
                    storage_path('app/public/'.$relPath),
                );

                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'disk' => 'public',
                    'path' => $relPath,
                    'sort_order' => $i,
                    'is_primary' => $i === 0,
                ]);
            }
        }
    }

    /**
     * Seeded catalog. Slug here is the unique-per-product part — final slug
     * is `sample-{slug}`.
     */
    private function products(): array
    {
        return [
            // Groceries
            [
                'category' => 'groceries',
                'slug' => 'premium-mealie-meal-10kg',
                'name' => 'Premium Mealie Meal 10kg',
                'description' => "Locally milled and fortified with vitamins A and D. Stone-ground for a richer flavour.\n\nBulk buyers — please contact us for case discounts.",
                'sku' => 'MM-10',
                'weight_kg' => 10.5,
                'price_tiers' => [
                    ['min_qty' => 1, 'unit_price' => 8.50],
                    ['min_qty' => 10, 'unit_price' => 7.75],
                    ['min_qty' => 50, 'unit_price' => 7.00],
                ],
                'variants' => [
                    ['color' => null, 'stock_quantity' => 240, 'sku' => 'MM-10-STD'],
                ],
            ],
            [
                'category' => 'groceries',
                'slug' => 'mazoe-orange-crush-2l',
                'name' => 'Mazoe Orange Crush 2L',
                'description' => "Zimbabwe's much-loved orange concentrate, ready to mix. 2L bottle.",
                'sku' => 'MAZ-2L',
                'weight_kg' => 2.1,
                'price_tiers' => [
                    ['min_qty' => 1, 'unit_price' => 4.50],
                    ['min_qty' => 12, 'unit_price' => 4.00],
                ],
                'variants' => [
                    ['color' => null, 'stock_quantity' => 480, 'sku' => 'MAZ-2L-STD'],
                ],
            ],
            // Fashion
            [
                'category' => 'fashion',
                'slug' => 'mukoko-slim-chinos',
                'name' => 'Mukoko Slim Chinos',
                'description' => 'Lightweight cotton-stretch chinos tailored for the Harare commute. Three colours.',
                'sku' => 'CHN-SLM',
                'weight_kg' => 0.6,
                'price_tiers' => [
                    ['min_qty' => 1, 'unit_price' => 22.00],
                    ['min_qty' => 5, 'unit_price' => 20.00],
                    ['min_qty' => 20, 'unit_price' => 18.00],
                ],
                'variants' => [
                    ['color' => 'Khaki', 'stock_quantity' => 60, 'sku' => 'CHN-SLM-KH'],
                    ['color' => 'Navy', 'stock_quantity' => 45, 'sku' => 'CHN-SLM-NV'],
                    ['color' => 'Olive', 'stock_quantity' => 30, 'sku' => 'CHN-SLM-OL'],
                ],
            ],
            [
                'category' => 'fashion',
                'slug' => 'zambezi-cotton-tee',
                'name' => 'Zambezi Cotton Tee',
                'description' => 'Soft 100% cotton everyday tee. Pre-shrunk; runs true to size.',
                'sku' => 'TEE-CTN',
                'weight_kg' => 0.25,
                'price_tiers' => [
                    ['min_qty' => 1, 'unit_price' => 12.00],
                    ['min_qty' => 10, 'unit_price' => 10.00],
                ],
                'variants' => [
                    ['color' => 'White', 'stock_quantity' => 80, 'sku' => 'TEE-CTN-WH'],
                    ['color' => 'Black', 'stock_quantity' => 80, 'sku' => 'TEE-CTN-BK'],
                    ['color' => 'Maroon', 'stock_quantity' => 60, 'sku' => 'TEE-CTN-MR'],
                    ['color' => 'Forest', 'stock_quantity' => 40, 'sku' => 'TEE-CTN-FR'],
                ],
            ],
            // Electronics
            [
                'category' => 'electronics',
                'slug' => 'afribuds-pro-earbuds',
                'name' => 'AfriBuds Pro Wireless Earbuds',
                'description' => 'Bluetooth 5.3, active noise cancellation, 30-hour battery with charging case. USB-C.',
                'sku' => 'EAR-AFP',
                'weight_kg' => 0.18,
                'price_tiers' => [
                    ['min_qty' => 1, 'unit_price' => 35.00],
                    ['min_qty' => 10, 'unit_price' => 32.00],
                ],
                'variants' => [
                    ['color' => 'Charcoal', 'stock_quantity' => 75, 'sku' => 'EAR-AFP-CH'],
                    ['color' => 'Pearl', 'stock_quantity' => 50, 'sku' => 'EAR-AFP-PR'],
                ],
            ],
            [
                'category' => 'electronics',
                'slug' => 'solarstrong-20w-panel',
                'name' => 'SolarStrong 20W Panel Kit',
                'description' => 'Portable monocrystalline 20W panel, controller, and accessories. Ideal for load-shedding backup at home.',
                'sku' => 'SOL-20W',
                'weight_kg' => 2.4,
                'price_tiers' => [
                    ['min_qty' => 1, 'unit_price' => 85.00],
                    ['min_qty' => 5, 'unit_price' => 80.00],
                    ['min_qty' => 20, 'unit_price' => 72.00],
                ],
                'variants' => [
                    ['color' => null, 'stock_quantity' => 35, 'sku' => 'SOL-20W-STD'],
                ],
            ],
            // Home & Living
            [
                'category' => 'home-living',
                'slug' => 'harare-reed-basket',
                'name' => 'Harare Handwoven Reed Basket',
                'description' => 'Locally handwoven storage basket. Each piece is unique. ~35 cm diameter.',
                'sku' => 'BAS-REED',
                'weight_kg' => 0.9,
                'price_tiers' => [
                    ['min_qty' => 1, 'unit_price' => 18.00],
                    ['min_qty' => 10, 'unit_price' => 15.00],
                ],
                'variants' => [
                    ['color' => 'Natural', 'stock_quantity' => 40, 'sku' => 'BAS-REED-NT'],
                    ['color' => 'Cocoa', 'stock_quantity' => 25, 'sku' => 'BAS-REED-CC'],
                ],
            ],
            [
                'category' => 'home-living',
                'slug' => 'maputi-ceramic-mug-set',
                'name' => 'Maputi Ceramic Mug Set (4)',
                'description' => 'Four 350ml ceramic mugs, dishwasher and microwave safe.',
                'sku' => 'MUG-SET4',
                'weight_kg' => 1.6,
                'price_tiers' => [
                    ['min_qty' => 1, 'unit_price' => 24.00],
                    ['min_qty' => 10, 'unit_price' => 21.00],
                ],
                'variants' => [
                    ['color' => 'Sand', 'stock_quantity' => 30, 'sku' => 'MUG-SET4-SA'],
                    ['color' => 'Sky', 'stock_quantity' => 25, 'sku' => 'MUG-SET4-SK'],
                    ['color' => 'Charcoal', 'stock_quantity' => 20, 'sku' => 'MUG-SET4-CH'],
                ],
            ],
            // Beauty
            [
                'category' => 'beauty',
                'slug' => 'baobab-body-butter-250ml',
                'name' => 'Baobab Body Butter 250ml',
                'description' => 'Whipped baobab + shea butter. Long-lasting hydration; vegan formulation.',
                'sku' => 'BTR-BAO',
                'weight_kg' => 0.35,
                'price_tiers' => [
                    ['min_qty' => 1, 'unit_price' => 14.00],
                    ['min_qty' => 6, 'unit_price' => 12.00],
                    ['min_qty' => 24, 'unit_price' => 10.00],
                ],
                'variants' => [
                    ['color' => null, 'stock_quantity' => 120, 'sku' => 'BTR-BAO-STD'],
                ],
            ],
            [
                'category' => 'beauty',
                'slug' => 'mubvee-shea-lip-balm',
                'name' => 'Mubvee Shea Lip Balm',
                'description' => 'Conditioning lip balm with shea butter and vitamin E. Long-wear, non-sticky.',
                'sku' => 'LIP-MBV',
                'weight_kg' => 0.04,
                'price_tiers' => [
                    ['min_qty' => 1, 'unit_price' => 4.00],
                    ['min_qty' => 12, 'unit_price' => 3.50],
                ],
                'variants' => [
                    ['color' => 'Plain', 'stock_quantity' => 200, 'sku' => 'LIP-MBV-PL'],
                    ['color' => 'Cocoa', 'stock_quantity' => 180, 'sku' => 'LIP-MBV-CC'],
                    ['color' => 'Berry', 'stock_quantity' => 140, 'sku' => 'LIP-MBV-BR'],
                    ['color' => 'Mint', 'stock_quantity' => 120, 'sku' => 'LIP-MBV-MN'],
                ],
            ],
            // Sports
            [
                'category' => 'sports',
                'slug' => 'mukurumudzi-cricket-ball',
                'name' => 'Mukurumudzi Cricket Ball (Match)',
                'description' => '156g match-grade leather cricket ball. Hand-stitched seam.',
                'sku' => 'CRK-BLL',
                'weight_kg' => 0.16,
                'price_tiers' => [
                    ['min_qty' => 1, 'unit_price' => 12.00],
                    ['min_qty' => 12, 'unit_price' => 10.00],
                    ['min_qty' => 48, 'unit_price' => 8.50],
                ],
                'variants' => [
                    ['color' => null, 'stock_quantity' => 96, 'sku' => 'CRK-BLL-STD'],
                ],
            ],
            // Books
            [
                'category' => 'books',
                'slug' => 'zimbabwe-at-45-pictorial',
                'name' => 'Zimbabwe at 45 — A Pictorial',
                'description' => 'Hardcover photo book covering four and a half decades of Zimbabwean life and landscapes.',
                'sku' => 'BK-ZIM45',
                'weight_kg' => 1.2,
                'price_tiers' => [
                    ['min_qty' => 1, 'unit_price' => 22.00],
                    ['min_qty' => 10, 'unit_price' => 18.00],
                ],
                'variants' => [
                    ['color' => null, 'stock_quantity' => 60, 'sku' => 'BK-ZIM45-HB'],
                ],
            ],
            // Toys
            [
                'category' => 'toys',
                'slug' => 'mufungo-wooden-blocks',
                'name' => 'Mufungo Wooden Block Set (50pc)',
                'description' => 'Untreated indigenous-timber block set in a canvas drawstring bag. Ages 3+.',
                'sku' => 'TOY-WB50',
                'weight_kg' => 2.2,
                'price_tiers' => [
                    ['min_qty' => 1, 'unit_price' => 30.00],
                    ['min_qty' => 10, 'unit_price' => 26.00],
                ],
                'variants' => [
                    ['color' => null, 'stock_quantity' => 40, 'sku' => 'TOY-WB50-STD'],
                ],
            ],
        ];
    }

    /**
     * GD-based generator: vertical gradient + accent corner + product name
     * wrapped over up to three lines. TTF text uses common Windows/Linux
     * font paths and falls back to GD's bitmap font when none is available.
     */
    private function generateImage(string $categorySlug, string $productName, string $categoryName, int $variantIndex, string $absoluteOutPath): void
    {
        @mkdir(dirname($absoluteOutPath), 0775, true);

        [$topHex, $bottomHex, $accentHex] = self::PALETTE[$categorySlug] ?? ['#0ea5e9', '#e0f2fe', '#075985'];

        $w = 1200;
        $h = 900;
        $img = imagecreatetruecolor($w, $h);

        // Vertical gradient.
        [$tr, $tg, $tb] = $this->hexToRgb($topHex);
        [$br, $bg, $bb] = $this->hexToRgb($bottomHex);
        for ($y = 0; $y < $h; $y++) {
            $t = $y / ($h - 1);
            $r = (int) round($tr + ($br - $tr) * $t);
            $g = (int) round($tg + ($bg - $tg) * $t);
            $b = (int) round($tb + ($bb - $tb) * $t);
            $line = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, $w, $y, $line);
        }

        // Accent corner triangle to differentiate per-variant image.
        [$ar, $ag, $ab] = $this->hexToRgb($accentHex);
        $accent = imagecolorallocatealpha($img, $ar, $ag, $ab, 70);
        $offset = 80 + $variantIndex * 120;
        imagefilledpolygon(
            $img,
            [$w - $offset, 0, $w, 0, $w, $offset],
            $accent,
        );

        // Decorative circles, slightly different per variant.
        $circle = imagecolorallocatealpha($img, 255, 255, 255, 105);
        imagefilledellipse($img, 200 + $variantIndex * 40, $h - 220, 360, 360, $circle);
        imagefilledellipse($img, $w - 240 - $variantIndex * 30, $h - 160, 220, 220, $circle);

        // White card panel for text.
        $card = imagecolorallocatealpha($img, 255, 255, 255, 30);
        $cardX1 = 90;
        $cardY1 = (int) ($h * 0.55);
        $cardX2 = $w - 90;
        $cardY2 = $h - 90;
        $this->roundedRect($img, $cardX1, $cardY1, $cardX2, $cardY2, 36, $card);

        $fontPath = $this->findFont();
        $darkText = imagecolorallocate($img, 17, 24, 39); // slate-900
        $subText = imagecolorallocate($img, 75, 85, 99);  // slate-600
        $brandText = imagecolorallocatealpha($img, 255, 255, 255, 70);

        if ($fontPath !== null) {
            $this->drawWrappedTtf($img, $fontPath, $productName, 46, $cardX1 + 36, $cardY1 + 70, $cardX2 - $cardX1 - 72, 60, $darkText);

            $catY = $cardY2 - 60;
            imagettftext($img, 22, 0, $cardX1 + 36, $catY, $subText, $fontPath, strtoupper($categoryName));

            // Top-left watermark.
            imagettftext($img, 28, 0, 60, 80, $brandText, $fontPath, 'delivo.');
        } else {
            // Fallback: GD's built-in bitmap fonts.
            $lines = $this->wrapBitmap($productName, 22);
            $y = $cardY1 + 60;
            foreach ($lines as $line) {
                imagestring($img, 5, $cardX1 + 36, $y, $line, $darkText);
                $y += 28;
            }
            imagestring($img, 4, $cardX1 + 36, $cardY2 - 50, strtoupper($categoryName), $subText);
            imagestring($img, 4, 60, 60, 'DELIVO', $brandText);
        }

        imagepng($img, $absoluteOutPath, 6);
        imagedestroy($img);
    }

    private function drawWrappedTtf($img, string $font, string $text, int $size, int $x, int $y, int $maxWidth, int $lineHeight, int $color): void
    {
        $words = preg_split('/\s+/', $text) ?: [$text];
        $line = '';
        $lines = [];

        foreach ($words as $word) {
            $candidate = $line === '' ? $word : "{$line} {$word}";
            $box = imagettfbbox($size, 0, $font, $candidate);
            $width = abs($box[2] - $box[0]);

            if ($width > $maxWidth && $line !== '') {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        // Cap to 3 lines, ellipsise the last.
        if (count($lines) > 3) {
            $lines = array_slice($lines, 0, 3);
            $lines[2] = rtrim($lines[2]).'…';
        }

        foreach ($lines as $i => $row) {
            imagettftext($img, $size, 0, $x, $y + ($i * $lineHeight), $color, $font, $row);
        }
    }

    private function wrapBitmap(string $text, int $maxChars): array
    {
        $words = preg_split('/\s+/', $text) ?: [$text];
        $line = '';
        $lines = [];
        foreach ($words as $word) {
            $candidate = $line === '' ? $word : "{$line} {$word}";
            if (strlen($candidate) > $maxChars && $line !== '') {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        return array_slice($lines, 0, 3);
    }

    private function roundedRect($img, int $x1, int $y1, int $x2, int $y2, int $radius, int $color): void
    {
        imagefilledrectangle($img, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
        imagefilledellipse($img, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }

    private function findFont(): ?string
    {
        $candidates = [
            'C:/Windows/Fonts/segoeuib.ttf',
            'C:/Windows/Fonts/arialbd.ttf',
            'C:/Windows/Fonts/arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/Library/Fonts/Arial Bold.ttf',
            '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
