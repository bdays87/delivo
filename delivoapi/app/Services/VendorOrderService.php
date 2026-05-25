<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
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
    /**
     * Statuses a vendor sees by default — payment confirmed onwards.
     */
    private const VISIBLE_STATUSES = [
        Order::STATUS_PAID,
        Order::STATUS_PICKED_UP,
        Order::STATUS_OUT_FOR_DELIVERY,
        Order::STATUS_DELIVERED,
        Order::STATUS_COMPLETED,
    ];

    public function listForVendor(Vendor $vendor, ?string $status = null): array
    {
        $query = OrderItem::query()
            ->with([
                'order:id,order_number,status,ship_city,ship_suburb,payment_confirmed_at,delivered_at,created_at,user_id',
                'order.user:id,name',
                'product:id,name,slug',
                'variant:id,color,sku',
                'influencer:id,display_name,slug',
            ])
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', function ($q) use ($status) {
                if ($status !== null && $status !== '') {
                    $q->where('status', $status);
                } else {
                    $q->whereIn('status', self::VISIBLE_STATUSES);
                }
            })
            ->orderByDesc('id');

        return $query->get()->map(fn (OrderItem $item) => $this->shape($item))->all();
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

    private function shape(OrderItem $item): array
    {
        $order = $item->order;
        $lineCommission = (float) $item->line_commission_usd_snapshot;
        $lineNet = round((float) $item->line_total_usd_snapshot - $lineCommission, 2);

        return [
            'id' => $item->id,
            'order' => $order ? [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'ship_city' => $order->ship_city,
                'ship_suburb' => $order->ship_suburb,
                'customer_name' => $order->user?->name,
                'payment_confirmed_at' => $order->payment_confirmed_at,
                'delivered_at' => $order->delivered_at,
                'created_at' => $order->created_at,
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
