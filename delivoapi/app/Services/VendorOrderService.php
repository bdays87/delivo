<?php

namespace App\Services;

use App\Interfaces\Repositories\IDeliveryZoneInterface;
use App\Models\CartItem;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderDeliveryShipment;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Vendor;

/**
 * Vendor's view of orders containing their products. Returns one row per
 * order_item with the order context the vendor needs to fulfill — but with
 * customer PII deliberately limited (name + delivery city only, no phone
 * or street). Default scope is PAID and beyond so vendors don't waste
 * effort on orders that may never settle.
 */
class VendorOrderService
{
    public function __construct(private readonly IDeliveryZoneInterface $zones) {}

    /**
     * Statuses a vendor sees by default. Includes PENDING_PAYMENT so vendors
     * can follow up with customers who haven't paid yet.
     */
    private const VISIBLE_STATUSES = [
        Order::STATUS_PENDING_PAYMENT,
        Order::STATUS_PAID,
        Order::STATUS_PICKED_UP,
        Order::STATUS_OUT_FOR_DELIVERY,
        Order::STATUS_DELIVERED,
        Order::STATUS_COMPLETED,
    ];

    public function listForVendor(Vendor $vendor, ?string $status = null, ?string $deliveryStatus = null, ?string $deliveryMethod = null): array
    {
        $query = OrderItem::query()
            ->with([
                'order:id,order_number,status,delivery_status,delivery_method,ship_city,ship_suburb,payment_confirmed_at,delivered_at,created_at,user_id',
                'order.user:id,name',
                'order.shipments:id,order_id,vendor_id,hub_id,hub_name_snapshot,hub_address_snapshot,dropoff_initiated_at,dropped_off_at',
                'product:id,name,slug',
                'variant:id,color,sku',
                'influencer:id,display_name,slug',
            ])
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) use ($status, $deliveryStatus, $deliveryMethod) {
                if ($status !== null && $status !== '') {
                    $q->where('status', $status);
                } else {
                    $q->whereIn('status', self::VISIBLE_STATUSES);
                }
                if ($deliveryStatus !== null && $deliveryStatus !== '') {
                    $q->where('delivery_status', $deliveryStatus);
                }
                if ($deliveryMethod !== null && $deliveryMethod !== '') {
                    $q->where('delivery_method', $deliveryMethod);
                }
            })
            ->orderByDesc('id');

        return $query->get()->map(fn (OrderItem $item) => $this->shape($item))->all();
    }

    /**
     * Hubs the vendor can choose from when initiating a dropoff. We use the
     * vendor's registered city — the same city their shipments originate from.
     */
    public function listDropoffHubs(Vendor $vendor): array
    {
        return $this->zones
            ->listActiveByCity((string) $vendor->city)
            ->map(fn (DeliveryZone $z) => [
                'id' => $z->id,
                'city' => $z->city,
                'name' => $z->hub_name,
                'address' => $z->hub_address,
            ])
            ->all();
    }

    /**
     * Self-pickup handover. Vendor enters the code the customer reads aloud;
     * the system validates it against order.delivery_code, then marks the
     * customer's receipt. Admin still has to close out the order to release
     * influencer earnings (consistent with the home-delivery rider flow).
     */
    public function confirmSelfPickup(Vendor $vendor, string $orderNumber, string $code): array
    {
        $order = Order::query()
            ->with('items', 'shipments')
            ->where('order_number', $orderNumber)
            ->first();

        if ($order === null) {
            return ['error' => 'Order not found.', 'code' => 404];
        }

        $vendorHasItem = OrderItem::query()
            ->where('order_id', $order->id)
            ->where('vendor_id', $vendor->id)
            ->exists();
        if (! $vendorHasItem) {
            return ['error' => 'This order has no items from your store.', 'code' => 403];
        }

        return app(OrderStatusService::class)->confirmSelfPickup($order, $code);
    }

    public function initiateDropoff(Vendor $vendor, int $shipmentId, int $hubId): array
    {
        $shipment = OrderDeliveryShipment::query()
            ->where('id', $shipmentId)
            ->where('vendor_id', $vendor->id)
            ->first();

        if ($shipment === null) {
            return ['error' => 'Shipment not found.', 'code' => 404];
        }
        if ($shipment->dropoff_initiated_at !== null) {
            return ['error' => 'Dropoff already initiated for this shipment.', 'code' => 422];
        }
        if ($shipment->dropped_off_at !== null) {
            return ['error' => 'This shipment was already received at the hub.', 'code' => 422];
        }
        if ($shipment->order && $shipment->order->status !== Order::STATUS_PAID) {
            return ['error' => 'Only paid orders can have dropoff initiated.', 'code' => 422];
        }

        // Validate hub belongs to a city the vendor can drop at.
        $hub = $this->zones->findById($hubId);
        if ($hub === null || $hub->status !== DeliveryZone::STATUS_ACTIVE) {
            return ['error' => 'Selected hub is not available.', 'code' => 422];
        }
        if (mb_strtolower(trim((string) $hub->city)) !== mb_strtolower(trim((string) $vendor->city))) {
            return ['error' => 'You can only drop off at hubs in your registered city.', 'code' => 422];
        }

        $shipment->forceFill([
            'hub_id' => $hub->id,
            'hub_name_snapshot' => $hub->hub_name,
            'hub_address_snapshot' => $hub->hub_address,
            'dropoff_initiated_at' => now(),
        ])->save();

        $order = $shipment->order()->with('shipments')->first();
        if ($order !== null) {
            app(OrderStatusService::class)->recomputeDeliveryStatus($order);
        }

        return ['shipment' => $shipment->fresh()];
    }

    public function summary(Vendor $vendor): array
    {
        $base = OrderItem::query()->where('vendor_id', $vendor->id);

        $pendingPaymentCount = (clone $base)
            ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_PENDING_PAYMENT))
            ->count();

        $paidCount = (clone $base)
            ->whereHas('order', fn ($q) => $q->whereIn('status', self::VISIBLE_STATUSES))
            ->count();

        $deliveredCount = (clone $base)
            ->whereHas('order', fn ($q) => $q->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]))
            ->count();

        $totalRevenue = (clone $base)
            ->whereHas('order', fn ($q) => $q->whereIn('status', self::VISIBLE_STATUSES))
            ->sum('line_total_usd_snapshot');

        $totalCommissionPaid = (clone $base)
            ->whereHas('order', fn ($q) => $q->whereIn('status', self::VISIBLE_STATUSES))
            ->whereNotNull('influencer_id')
            ->sum('line_commission_usd_snapshot');

        return [
            'pending_payment_count' => $pendingPaymentCount,
            'paid_count' => $paidCount,
            'delivered_count' => $deliveredCount,
            'gross_revenue_usd' => number_format((float) $totalRevenue, 2, '.', ''),
            'influencer_commission_paid_usd' => number_format((float) $totalCommissionPaid, 2, '.', ''),
            'net_after_commission_usd' => number_format(max(0, (float) $totalRevenue - (float) $totalCommissionPaid), 2, '.', ''),
        ];
    }

    /**
     * Active customer carts containing this vendor's products. Each cart is
     * returned as one row with the customer + their contact info + the subset
     * of cart items that belong to this vendor + the value of those items.
     * Powers the abandoned-cart outreach flow.
     */
    public function cartsForVendor(Vendor $vendor): array
    {
        $items = CartItem::query()
            ->with([
                'cart:id,user_id,updated_at',
                'cart.user:id,name,email,phone',
                'product:id,name,slug,vendor_id',
                'product.priceTiers:id,product_id,min_qty,unit_price',
                'variant:id,color,sku,stock_quantity',
            ])
            ->whereHas('product', fn ($q) => $q
                ->where('vendor_id', $vendor->id)
                ->where('status', Product::STATUS_ACTIVE))
            ->orderByDesc('id')
            ->get();

        $byCart = $items->groupBy('cart_id');
        $rows = [];

        foreach ($byCart as $cartId => $cartItems) {
            $first = $cartItems->first();
            $cart = $first->cart;
            if ($cart === null || $cart->user === null) {
                continue;
            }

            $vendorTotal = 0.0;
            $shapedItems = [];
            $oldestUpdated = null;

            foreach ($cartItems as $ci) {
                $unit = $this->resolveProductUnitPrice($ci->product, (int) $ci->quantity);
                $line = round($unit * $ci->quantity, 2);
                $vendorTotal += $line;

                if ($oldestUpdated === null || ($ci->updated_at && $ci->updated_at->lt($oldestUpdated))) {
                    $oldestUpdated = $ci->updated_at;
                }

                $shapedItems[] = [
                    'id' => $ci->id,
                    'product' => $ci->product ? [
                        'id' => $ci->product->id,
                        'name' => $ci->product->name,
                        'slug' => $ci->product->slug,
                    ] : null,
                    'variant' => $ci->variant ? [
                        'id' => $ci->variant->id,
                        'color' => $ci->variant->color,
                        'sku' => $ci->variant->sku,
                        'stock_quantity' => (int) $ci->variant->stock_quantity,
                    ] : null,
                    'quantity' => (int) $ci->quantity,
                    'unit_price_usd' => number_format($unit, 2, '.', ''),
                    'line_total_usd' => number_format($line, 2, '.', ''),
                    'updated_at' => $ci->updated_at,
                ];
            }

            $rows[] = [
                'cart_id' => $cartId,
                'customer' => [
                    'id' => $cart->user->id,
                    'name' => $cart->user->name,
                    'email' => $cart->user->email,
                    'phone' => $cart->user->phone,
                ],
                'items' => $shapedItems,
                'vendor_total_usd' => number_format($vendorTotal, 2, '.', ''),
                'item_count' => array_sum(array_column($shapedItems, 'quantity')),
                'oldest_item_at' => $oldestUpdated,
                'cart_updated_at' => $cart->updated_at,
            ];
        }

        // Oldest carts first — those are the abandoned ones most worth chasing.
        usort($rows, fn ($a, $b) => ($a['oldest_item_at'] <=> $b['oldest_item_at']));

        return $rows;
    }

    private function resolveProductUnitPrice(?Product $product, int $qty): float
    {
        if ($product === null) {
            return 0.0;
        }
        $tiers = $product->priceTiers ?? collect();
        if ($tiers->isEmpty()) {
            return 0.0;
        }
        $sorted = $tiers->sortBy(fn ($t) => (int) $t->min_qty)->values();
        $unit = (float) $sorted->first()->unit_price;
        foreach ($sorted as $tier) {
            if ($qty >= (int) $tier->min_qty) {
                $unit = (float) $tier->unit_price;
            }
        }

        return $unit;
    }

    private function shape(OrderItem $item): array
    {
        $order = $item->order;
        $lineCommission = (float) $item->line_commission_usd_snapshot;
        $lineNet = round((float) $item->line_total_usd_snapshot - $lineCommission, 2);

        $shipment = $order?->shipments?->firstWhere('vendor_id', $item->vendor_id);

        return [
            'id' => $item->id,
            'order' => $order ? [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'delivery_status' => $order->delivery_status,
                'delivery_method' => $order->delivery_method,
                'ship_city' => $order->ship_city,
                'ship_suburb' => $order->ship_suburb,
                'customer_name' => $order->user?->name,
                'payment_confirmed_at' => $order->payment_confirmed_at,
                'delivered_at' => $order->delivered_at,
                'created_at' => $order->created_at,
            ] : null,
            'shipment' => $shipment ? [
                'id' => $shipment->id,
                'hub_id' => $shipment->hub_id,
                'hub_name' => $shipment->hub_name_snapshot,
                'hub_address' => $shipment->hub_address_snapshot,
                'dropoff_initiated_at' => $shipment->dropoff_initiated_at,
                'dropped_off_at' => $shipment->dropped_off_at,
            ] : null,
            'product' => $item->product ? [
                'id' => $item->product->id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
            ] : null,
            'variant' => $item->variant ? [
                'id' => $item->variant->id,
                'color' => $item->variant->color,
                'sku' => $item->variant->sku,
            ] : null,
            'quantity' => (int) $item->quantity,
            'unit_price_usd' => $item->unit_price_usd_snapshot,
            'line_total_usd' => $item->line_total_usd_snapshot,
            'influencer' => $item->influencer ? [
                'id' => $item->influencer->id,
                'display_name' => $item->influencer->display_name,
                'slug' => $item->influencer->slug,
            ] : null,
            'influencer_commission_usd' => $item->line_commission_usd_snapshot,
            'vendor_net_usd' => number_format($lineNet, 2, '.', ''),
        ];
    }
}
