<?php

namespace App\Services;

use App\Interfaces\Repositories\IAddressInterface;
use App\Interfaces\Repositories\ICartInterface;
use App\Interfaces\Repositories\IExchangeRateInterface;
use App\Interfaces\Repositories\IOrderInterface;
use App\Models\MobileWallet;
use App\Models\Order;
use App\Models\OrderDeliveryShipment;
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
        private readonly PricingService $pricing,
        private readonly ShipmentAssignmentService $assigner,
        private readonly CartCouponService $couponSvc,
    ) {}

    /**
     * Live breakdown for the user's cart against a chosen delivery address.
     * Stock is NOT validated here — that's place-order's job.
     */
    public function quote(User $user, int $addressId): array
    {
        $cart = $this->carts->loadWithItems($this->carts->findOrCreateForUser($user->id));
        if ($cart->items->isEmpty()) {
            return ['error' => 'Your cart is empty.', 'code' => 422];
        }

        $address = $this->addresses->findForUser($addressId, $user->id);
        if ($address === null) {
            return ['error' => 'Delivery address not found.', 'code' => 422];
        }

        $subtotal = 0.0;
        foreach ($cart->items as $item) {
            if ($item->product === null || $item->variant === null) {
                continue;
            }
            $unit = $this->resolveUnitPrice($item->product, (int) $item->quantity);
            $subtotal += $unit * (int) $item->quantity;
        }
        $subtotal = round($subtotal, 2);

        return ['quote' => $this->pricing->quote($cart, $subtotal, $address)];
    }

    /**
     * Place an order from the user's cart. Hard-blocks on stock-out OR on any
     * uncovered shipment (vendor city has no hub, no route, no fee band).
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
            $lineGross = round($unit * $item->quantity, 2);

            $lines[] = [
                'item' => $item,
                'product' => $product,
                'variant' => $variant,
                'unit' => $unit,
                'line_gross' => $lineGross,
            ];
        }

        if (! empty($stockIssues)) {
            return [
                'error' => 'Some items are no longer available in the quantities requested.',
                'code' => 409,
                'lines' => $stockIssues,
            ];
        }

        // Coverage + per-vendor shipment fees. Recomputed server-side at
        // place-time; the client breakdown is for display only.
        $shipmentResult = $this->pricing->shipmentsForCart($cart, $address);
        if (! $shipmentResult['all_covered']) {
            $uncovered = collect($shipmentResult['shipments'])
                ->where('is_covered', false)
                ->pluck('reason')
                ->filter()
                ->unique()
                ->implode(' · ');

            return [
                'error' => $uncovered !== ''
                    ? $uncovered
                    : 'We cannot deliver some items to this address.',
                'code' => 422,
                'shipments' => $shipmentResult['shipments'],
            ];
        }

        $shipments = $shipmentResult['shipments'];

        // Resolve the applied coupon — refusal here means the cart's stored
        // code is no longer valid (item removed, etc.); it's been cleared.
        $coupon = $this->couponSvc->resolveActive($cart);

        return DB::transaction(function () use ($user, $address, $wallet, $lines, $shipments, $coupon) {
            // Per-line discount snapshots — only the cart line tied to the
            // coupon's product gets the buyer discount + influencer commission.
            $totalDiscount = 0.0;
            $totalCommission = 0.0;
            foreach ($lines as $idx => $line) {
                $applies = $coupon !== null
                    && $coupon->product_id !== null
                    && (int) $coupon->product_id === (int) $line['product']->id;
                $buyerPct = $applies ? (float) $coupon->buyer_discount_pct : 0.0;
                $commissionPct = $applies ? (float) $coupon->influencer_commission_pct : 0.0;
                $lineDiscount = round((float) $line['line_gross'] * ($buyerPct / 100), 2);
                $lineCommission = round((float) $line['line_gross'] * ($commissionPct / 100), 2);
                $lineTotal = round((float) $line['line_gross'] - $lineDiscount, 2);

                $lines[$idx]['line_discount'] = $lineDiscount;
                $lines[$idx]['line_commission'] = $lineCommission;
                $lines[$idx]['line_total'] = $lineTotal;
                $lines[$idx]['buyer_pct'] = $buyerPct;
                $lines[$idx]['commission_pct'] = $commissionPct;
                $lines[$idx]['influencer_id'] = $applies ? $coupon->influencer_id : null;

                $totalDiscount += $lineDiscount;
                $totalCommission += $lineCommission;
            }

            $subtotal = round(array_sum(array_column($lines, 'line_total')), 2);
            $serviceCharge = round($this->pricing->serviceChargeFor($subtotal), 2);
            $shipping = round(
                array_sum(array_map(fn ($s) => (float) ($s['fee_usd'] ?? 0), $shipments)),
                2,
            );
            $total = round($subtotal + $serviceCharge + $shipping, 2);

            $year = (int) date('Y');
            $seq = $this->orders->nextSequenceForYear($year);
            $orderNumber = sprintf('DLV-%02d-%06d', $year % 100, $seq);

            $rate = $this->rates->find('USD', 'ZWG');

            $order = Order::query()->create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'address_id' => $address->id,
                'mobile_wallet_id' => $wallet->id,
                'applied_coupon_id' => $coupon?->id,
                'applied_coupon_code' => $coupon?->code,
                'ship_recipient_name' => $address->recipient_name,
                'ship_recipient_phone' => $address->recipient_phone,
                'ship_city' => $address->city,
                'ship_suburb' => $address->suburb,
                'ship_street' => $address->street,
                'ship_notes' => $address->notes,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal_usd' => $subtotal,
                'total_buyer_discount_usd' => round($totalDiscount, 2),
                'total_influencer_commission_usd' => round($totalCommission, 2),
                'service_charge_usd' => $serviceCharge,
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
                    'influencer_id' => $line['influencer_id'],
                    'product_name_snapshot' => $line['product']->name,
                    'color_snapshot' => $line['variant']->color,
                    'quantity' => $line['item']->quantity,
                    'unit_price_usd_snapshot' => $line['unit'],
                    'buyer_discount_pct_snapshot' => $line['buyer_pct'],
                    'line_discount_usd_snapshot' => $line['line_discount'],
                    'influencer_commission_pct_snapshot' => $line['commission_pct'],
                    'line_commission_usd_snapshot' => $line['line_commission'],
                    'line_total_usd_snapshot' => $line['line_total'],
                ]);

                $line['variant']->decrement('stock_quantity', (int) $line['item']->quantity);

                $line['item']->delete();
            }

            // Bump coupon usage + clear from the cart row (the order owns its
            // own snapshot from here on).
            if ($coupon !== null) {
                $coupon->increment('usage_count');
                $cart->forceFill(['applied_coupon_code' => null])->save();
            }

            // Snapshot per-vendor shipments so admins and customers can audit
            // each leg even if hub/band data drifts later.
            foreach ($shipments as $s) {
                OrderDeliveryShipment::query()->create([
                    'order_id' => $order->id,
                    'vendor_id' => $s['vendor_id'],
                    'hub_id' => $s['hub']['id'] ?? null,
                    'hub_name_snapshot' => $s['hub']['name'] ?? null,
                    'hub_address_snapshot' => $s['hub']['address'] ?? null,
                    'distance_km' => $s['distance_km'],
                    'fee_usd' => $s['fee_usd'],
                    'delivery_fee_id' => $s['band_id'],
                ]);
            }

            // Match each shipment to a delivery provider whose coverage
            // includes both the hub and destination cities. Shipments that
            // don't match remain AWAITING_PROVIDER for admin assignment.
            $this->assigner->assignForOrder($order->fresh('shipments'));

            return [
                'order' => $order->load(['items', 'shipments', 'mobileWallet:id,name,code']),
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
