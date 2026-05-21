<?php

namespace App\Services;

use App\Interfaces\Repositories\IAddressInterface;
use App\Interfaces\Repositories\ICartInterface;
use App\Interfaces\Repositories\IExchangeRateInterface;
use App\Interfaces\Repositories\IOrderInterface;
use App\Models\MobileWallet;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private readonly ICartInterface $carts,
        private readonly IAddressInterface $addresses,
        private readonly IOrderInterface $orders,
        private readonly IExchangeRateInterface $rates,
    ) {}

    /**
     * Place an order from the user's cart. Hard-blocks on stock-out (returns
     * an error payload with the offending lines) and returns the created order
     * with its snapshotted items.
     */
    public function place(User $user, int $addressId, int $mobileWalletId): array
    {
        $cart = $this->carts->loadWithItems($this->carts->findOrCreateForUser($user->id));
        if ($cart->items->isEmpty()) {
            return ['error' => 'Your cart is empty.', 'code' => 422];
        }

        $address = $this->addresses->findForUser($addressId, $user->id);
        if ($address === null) {
            return ['error' => 'Delivery address not found.', 'code' => 422];
        }

        $wallet = MobileWallet::query()->where('status', MobileWallet::STATUS_ACTIVE)->find($mobileWalletId);
        if ($wallet === null) {
            return ['error' => 'Selected payment wallet is not available.', 'code' => 422];
        }

        // Stock validation pass — hard-block before mutating anything.
        $stockIssues = [];
        $lines = [];

        foreach ($cart->items as $item) {
            /** @var ProductVariant|null $variant */
            $variant = $item->variant;
            /** @var Product|null $product */
            $product = $item->product;

            if ($variant === null || $product === null || $product->status !== Product::STATUS_ACTIVE) {
                $stockIssues[] = [
                    'cart_item_id' => $item->id,
                    'reason' => 'This item is no longer available.',
                ];

                continue;
            }

            if ((int) $item->quantity > (int) $variant->stock_quantity) {
                $stockIssues[] = [
                    'cart_item_id' => $item->id,
                    'product_name' => $product->name,
                    'requested' => (int) $item->quantity,
                    'available' => (int) $variant->stock_quantity,
                ];

                continue;
            }

            $unit = $this->resolveUnitPrice($product, (int) $item->quantity);
            $lineTotal = round($unit * $item->quantity, 2);

            $lines[] = [
                'item' => $item,
                'product' => $product,
                'variant' => $variant,
                'unit' => $unit,
                'line_total' => $lineTotal,
            ];
        }

        if (! empty($stockIssues)) {
            return [
                'error' => 'Some items are no longer available in the quantities requested.',
                'code' => 409,
                'lines' => $stockIssues,
            ];
        }

        return DB::transaction(function () use ($user, $address, $wallet, $lines) {
            $subtotal = round(array_sum(array_column($lines, 'line_total')), 2);
            $shipping = 0.00; // platform flat fee — admin Settings adds this in slice 10
            $total = round($subtotal + $shipping, 2);

            $year = (int) date('Y');
            $seq = $this->orders->nextSequenceForYear($year);
            $orderNumber = sprintf('DLV-%02d-%06d', $year % 100, $seq);

            $rate = $this->rates->find('USD', 'ZWG');

            $order = Order::query()->create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'address_id' => $address->id,
                'mobile_wallet_id' => $wallet->id,
                'ship_recipient_name' => $address->recipient_name,
                'ship_recipient_phone' => $address->recipient_phone,
                'ship_city' => $address->city,
                'ship_suburb' => $address->suburb,
                'ship_street' => $address->street,
                'ship_notes' => $address->notes,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal_usd' => $subtotal,
                'shipping_usd' => $shipping,
                'total_usd' => $total,
                'usd_to_zwg_rate' => $rate?->rate,
                'payment_reference' => $orderNumber,
            ]);

            foreach ($lines as $line) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'vendor_id' => $line['product']->vendor_id,
                    'product_id' => $line['product']->id,
                    'product_variant_id' => $line['variant']->id,
                    'product_name_snapshot' => $line['product']->name,
                    'color_snapshot' => $line['variant']->color,
                    'quantity' => $line['item']->quantity,
                    'unit_price_usd_snapshot' => $line['unit'],
                    'line_total_usd_snapshot' => $line['line_total'],
                ]);

                // Decrement variant stock.
                $line['variant']->decrement('stock_quantity', (int) $line['item']->quantity);

                $line['item']->delete();
            }

            return [
                'order' => $order->load(['items', 'mobileWallet:id,name,code']),
            ];
        });
    }

    private function resolveUnitPrice(Product $product, int $qty): float
    {
        $tiers = $product->priceTiers->sortBy(fn ($t) => (int) $t->min_qty)->values();
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
}
