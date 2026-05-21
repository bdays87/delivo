<?php

namespace App\Services;

use App\Interfaces\Repositories\ICartInterface;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(private readonly ICartInterface $repo) {}

    public function currentForUser(User $user): Cart
    {
        return $this->repo->loadWithItems($this->repo->findOrCreateForUser($user->id));
    }

    /**
     * Build the cart payload the API returns. Computes a live unit price per
     * line by resolving the line's quantity against the parent product's tier
     * list. Tiers apply per-line.
     */
    public function snapshot(Cart $cart): array
    {
        $cart = $this->repo->loadWithItems($cart);
        $lines = [];
        $subtotal = 0.0;

        foreach ($cart->items as $item) {
            $unit = $this->resolveUnitPrice($item->product, (int) $item->quantity);
            $lineTotal = round($unit * $item->quantity, 2);
            $subtotal += $lineTotal;

            $lines[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'product' => $item->product ? [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'slug' => $item->product->slug,
                    'status' => $item->product->status,
                    'vendor' => $item->product->vendor ? [
                        'id' => $item->product->vendor->id,
                        'business_name' => $item->product->vendor->business_name,
                    ] : null,
                    'primary_image' => $this->primaryImagePath($item->product),
                ] : null,
                'variant' => $item->variant ? [
                    'id' => $item->variant->id,
                    'color' => $item->variant->color,
                    'stock_quantity' => $item->variant->stock_quantity,
                    'sku' => $item->variant->sku,
                ] : null,
                'quantity' => $item->quantity,
                'unit_price_usd' => number_format($unit, 2, '.', ''),
                'line_total_usd' => number_format($lineTotal, 2, '.', ''),
                'stock_warning' => $item->variant && $item->quantity > (int) $item->variant->stock_quantity,
            ];
        }

        return [
            'id' => $cart->id,
            'items' => $lines,
            'item_count' => array_sum(array_column($lines, 'quantity')),
            'subtotal_usd' => number_format($subtotal, 2, '.', ''),
        ];
    }

    public function addItem(User $user, ProductVariant $variant, int $quantity): CartItem
    {
        $cart = $this->repo->findOrCreateForUser($user->id);

        return DB::transaction(function () use ($cart, $variant, $quantity) {
            $existing = $this->repo->findItem($cart->id, $variant->id);

            if ($existing !== null) {
                $existing->quantity = (int) $existing->quantity + $quantity;
                $existing->save();

                return $existing;
            }

            return CartItem::query()->create([
                'cart_id' => $cart->id,
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'quantity' => $quantity,
            ]);
        });
    }

    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        $item->quantity = $quantity;
        $item->save();

        return $item;
    }

    public function removeItem(CartItem $item): bool
    {
        return (bool) $item->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }

    private function resolveUnitPrice(?Product $product, int $qty): float
    {
        if ($product === null) {
            return 0.0;
        }

        $tiers = ($product->priceTiers ?? collect())
            ->sortBy(fn ($t) => (int) $t->min_qty)
            ->values();

        if ($tiers->isEmpty()) {
            return 0.0;
        }

        $unit = (float) $tiers->first()->unit_price;
        foreach ($tiers as $tier) {
            if ($qty >= (int) $tier->min_qty) {
                $unit = (float) $tier->unit_price;
            }
        }

        return $unit;
    }

    private function primaryImagePath(?Product $product): ?array
    {
        if ($product === null || ! $product->relationLoaded('images')) {
            return null;
        }
        $primary = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
        if ($primary === null) {
            return null;
        }

        return ['disk' => $primary->disk, 'path' => $primary->path];
    }
}
