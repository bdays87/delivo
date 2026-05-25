<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Collection;

class VendorDashboardService
{
    private const LOW_STOCK_THRESHOLD = 5;

    public function __construct(
        private readonly VendorProductService $products,
        private readonly VendorOrderService $orders,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Vendor $vendor): array
    {
        $catalog = $this->products->listForVendor($vendor, null);
        $orderSummary = $this->orders->summary($vendor);

        $summary = $this->buildSummary($catalog, $orderSummary);
        $stock = $this->buildStockSummary($catalog);
        $topMoving = $this->buildTopMoving($catalog);
        $stockByProduct = $this->buildStockByProduct($catalog);

        return [
            'summary' => $summary,
            'stock' => $stock,
            'top_moving' => $topMoving,
            'stock_by_product' => $stockByProduct,
        ];
    }

    /**
     * @param  Collection<int, Product>  $catalog
     * @return array<string, mixed>
     */
    private function buildSummary(Collection $catalog, array $orderSummary): array
    {
        $byStatus = $catalog->groupBy('status')->map->count();

        return [
            'total_products' => $catalog->count(),
            'active_products' => (int) ($byStatus[Product::STATUS_ACTIVE] ?? 0),
            'pending_products' => (int) ($byStatus[Product::STATUS_PENDING] ?? 0),
            'total_orders' => (int) $orderSummary['paid_count'],
            'pending_payment_orders' => (int) $orderSummary['pending_payment_count'],
            'delivered_orders' => (int) $orderSummary['delivered_count'],
            'total_sales_usd' => $orderSummary['gross_revenue_usd'],
            'commission_paid_usd' => $orderSummary['influencer_commission_paid_usd'],
            'net_after_commission_usd' => $orderSummary['net_after_commission_usd'],
            'orders_available' => true,
        ];
    }

    /**
     * @param  Collection<int, Product>  $catalog
     * @return array<string, int>
     */
    private function buildStockSummary(Collection $catalog): array
    {
        $totalUnits = 0;
        $variantCount = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;

        foreach ($catalog as $product) {
            foreach ($product->variants as $variant) {
                $qty = (int) $variant->stock_quantity;
                $variantCount++;
                $totalUnits += $qty;

                if ($qty === 0) {
                    $outOfStockCount++;
                } elseif ($qty <= self::LOW_STOCK_THRESHOLD) {
                    $lowStockCount++;
                }
            }
        }

        return [
            'total_units' => $totalUnits,
            'variant_count' => $variantCount,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'healthy_count' => max(0, $variantCount - $lowStockCount - $outOfStockCount),
        ];
    }

    /**
     * Rank products by demand signals until order history exists:
     * low-stock variants first, then lower remaining stock.
     *
     * @param  Collection<int, Product>  $catalog
     * @return list<array<string, mixed>>
     */
    private function buildTopMoving(Collection $catalog): array
    {
        return $catalog
            ->map(function (Product $product) {
                $variants = $product->variants;
                $stockRemaining = $variants->sum('stock_quantity');
                $lowStockVariants = $variants->filter(
                    fn ($v) => $v->stock_quantity > 0 && $v->stock_quantity <= self::LOW_STOCK_THRESHOLD,
                )->count();
                $outOfStockVariants = $variants->filter(fn ($v) => $v->stock_quantity === 0)->count();
                $movementScore = ($lowStockVariants * 10) + ($outOfStockVariants * 5) + max(0, 50 - $stockRemaining);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'status' => $product->status,
                    'category_name' => $product->category?->name,
                    'image_path' => $this->primaryImagePath($product),
                    'units_sold' => 0,
                    'revenue_usd' => '0.00',
                    'stock_remaining' => (int) $stockRemaining,
                    'low_stock_variants' => $lowStockVariants,
                    'movement_score' => $movementScore,
                ];
            })
            ->sortByDesc('movement_score')
            ->values()
            ->take(20)
            ->map(fn (array $row, int $index) => array_merge($row, ['movement_rank' => $index + 1]))
            ->all();
    }

    /**
     * @param  Collection<int, Product>  $catalog
     * @return list<array<string, mixed>>
     */
    private function buildStockByProduct(Collection $catalog): array
    {
        return $catalog
            ->map(function (Product $product) {
                $variants = $product->variants;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'status' => $product->status,
                    'total_stock' => (int) $variants->sum('stock_quantity'),
                    'variant_count' => $variants->count(),
                    'low_stock_variants' => $variants->filter(
                        fn ($v) => $v->stock_quantity > 0 && $v->stock_quantity <= self::LOW_STOCK_THRESHOLD,
                    )->count(),
                    'out_of_stock_variants' => $variants->filter(fn ($v) => $v->stock_quantity === 0)->count(),
                ];
            })
            ->sortBy('total_stock')
            ->values()
            ->all();
    }

    private function primaryImagePath(Product $product): ?string
    {
        $images = $product->images;

        if ($images->isEmpty()) {
            return null;
        }

        $primary = $images->firstWhere('is_primary', true) ?? $images->first();

        return $primary?->path;
    }
}
