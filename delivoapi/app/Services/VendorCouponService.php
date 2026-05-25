<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Vendor;

/**
 * Vendor's view of coupons (and therefore influencers) attached to their
 * products. One row per coupon. Stats are computed from order_items where the
 * coupon was applied to a confirmed order so vendors see lifetime impact:
 * units moved, revenue, commission paid out.
 */
class VendorCouponService
{
    private const SETTLED_STATUSES = [
        Order::STATUS_PAID,
        Order::STATUS_PICKED_UP,
        Order::STATUS_OUT_FOR_DELIVERY,
        Order::STATUS_DELIVERED,
        Order::STATUS_COMPLETED,
    ];

    public function listForVendor(Vendor $vendor): array
    {
        $coupons = Coupon::query()
            ->with([
                'product:id,name,slug,vendor_id',
                'influencer:id,display_name,slug,contact_email',
            ])
            ->where('vendor_id', $vendor->id)
            ->orderByDesc('id')
            ->get();

        return $coupons->map(fn (Coupon $c) => $this->shape($c))->all();
    }

    public function summary(Vendor $vendor): array
    {
        $coupons = Coupon::query()
            ->where('vendor_id', $vendor->id)
            ->get();

        $totalUsage = (int) $coupons->sum('usage_count');

        // Aggregate revenue + commission from settled order_items where the
        // order applied one of this vendor's coupons.
        $couponIds = $coupons->pluck('id')->all();
        $items = OrderItem::query()
            ->whereIn('order_id', function ($q) use ($couponIds) {
                $q->select('id')
                    ->from('orders')
                    ->whereIn('applied_coupon_id', $couponIds)
                    ->whereIn('status', self::SETTLED_STATUSES);
            })
            ->where('vendor_id', $vendor->id)
            ->whereNotNull('influencer_id')
            ->get();

        $revenue = (float) $items->sum('line_total_usd_snapshot');
        $commission = (float) $items->sum('line_commission_usd_snapshot');

        return [
            'coupon_count' => $coupons->count(),
            'influencer_count' => $coupons->pluck('influencer_id')->filter()->unique()->count(),
            'total_redemptions' => $totalUsage,
            'revenue_via_codes_usd' => number_format($revenue, 2, '.', ''),
            'commission_paid_usd' => number_format($commission, 2, '.', ''),
        ];
    }

    private function shape(Coupon $coupon): array
    {
        $settledItems = OrderItem::query()
            ->whereHas('order', fn ($q) => $q
                ->where('applied_coupon_id', $coupon->id)
                ->whereIn('status', self::SETTLED_STATUSES))
            ->where('vendor_id', $coupon->product?->vendor_id)
            ->whereNotNull('influencer_id')
            ->selectRaw('SUM(line_total_usd_snapshot) as revenue, SUM(line_commission_usd_snapshot) as commission, SUM(quantity) as units')
            ->first();

        return [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'status' => $coupon->status,
            'buyer_discount_pct' => $coupon->buyer_discount_pct,
            'influencer_commission_pct' => $coupon->influencer_commission_pct,
            'usage_count' => (int) $coupon->usage_count,
            'usage_limit' => $coupon->usage_limit,
            'product' => $coupon->product ? [
                'id' => $coupon->product->id,
                'name' => $coupon->product->name,
                'slug' => $coupon->product->slug,
            ] : null,
            'influencer' => $coupon->influencer ? [
                'id' => $coupon->influencer->id,
                'display_name' => $coupon->influencer->display_name,
                'slug' => $coupon->influencer->slug,
                'contact_email' => $coupon->influencer->contact_email,
            ] : null,
            'units_sold' => (int) ($settledItems->units ?? 0),
            'revenue_usd' => number_format((float) ($settledItems->revenue ?? 0), 2, '.', ''),
            'commission_paid_usd' => number_format((float) ($settledItems->commission ?? 0), 2, '.', ''),
        ];
    }
}
