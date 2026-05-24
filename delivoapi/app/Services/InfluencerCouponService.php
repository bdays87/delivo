<?php

namespace App\Services;

use App\Interfaces\Repositories\ICouponInterface;
use App\Models\Coupon;
use App\Models\Influencer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class InfluencerCouponService
{
    public function __construct(private readonly ICouponInterface $coupons) {}

    /**
     * Returns the influencer's existing code for a product, or generates one
     * if none exists. Idempotent. Requires: influencer ACTIVE, product
     * ACTIVE, product has influencer commission > 0.
     *
     * @return array{coupon: Coupon}|array{error: string, code: int}
     */
    public function getOrCreateForProduct(Influencer $influencer, Product $product): array
    {
        if ($influencer->status !== Influencer::STATUS_ACTIVE) {
            return ['error' => 'Your influencer account is not active.', 'code' => 403];
        }
        if ($product->status !== Product::STATUS_ACTIVE) {
            return ['error' => 'This product is not currently available for promotion.', 'code' => 422];
        }
        if ((float) $product->affiliate_influencer_pct <= 0) {
            return ['error' => 'This product does not pay an influencer commission.', 'code' => 422];
        }

        $existing = $this->coupons->findForInfluencerProduct($influencer->id, $product->id);
        if ($existing !== null) {
            return ['coupon' => $existing];
        }

        $coupon = $this->coupons->create([
            'code' => $this->generateUniqueCode($influencer, $product),
            'vendor_id' => $product->vendor_id,
            'product_id' => $product->id,
            'influencer_id' => $influencer->id,
            'buyer_discount_pct' => (float) $product->affiliate_buyer_discount_pct,
            'influencer_commission_pct' => (float) $product->affiliate_influencer_pct,
            'status' => Coupon::STATUS_ACTIVE,
        ]);

        return ['coupon' => $coupon];
    }

    public function listForInfluencer(Influencer $influencer): Collection
    {
        return $this->coupons->listForInfluencer($influencer->id);
    }

    /**
     * Code shape: {INFLUENCER_SLUG_PREFIX}{PRODUCT_SLUG_TOKEN}{RANDOM}, e.g.
     * TARIROMEALIE7F3A — short enough to share, distinctive enough to be
     * memorable per influencer.
     */
    private function generateUniqueCode(Influencer $influencer, Product $product): string
    {
        $infPart = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $influencer->slug), 0, 6));
        $prodPart = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $product->slug), 0, 6));

        do {
            $tail = Str::upper(Str::random(4));
            $code = $infPart.$prodPart.$tail;
        } while ($this->coupons->codeExists($code));

        return $code;
    }
}
