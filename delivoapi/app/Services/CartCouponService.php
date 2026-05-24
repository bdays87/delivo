<?php

namespace App\Services;

use App\Interfaces\Repositories\ICouponInterface;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\User;

class CartCouponService
{
    public function __construct(private readonly ICouponInterface $coupons) {}

    /**
     * Validate and attach a coupon code to the user's cart. Returns the
     * resolved coupon on success, or an error payload the controller turns
     * into a 422.
     *
     * Validation:
     *  - Code exists and is ACTIVE.
     *  - Cart contains the product the coupon is tied to (influencer codes
     *    are product-scoped — Stage 2 design).
     *  - Customer is not the influencer themselves (no self-attribution).
     *  - Usage limit not exhausted (when set).
     */
    public function apply(User $user, Cart $cart, string $code): array
    {
        $code = trim(mb_strtoupper($code));
        $coupon = $this->coupons->findByCode($code);

        if ($coupon === null || $coupon->status !== Coupon::STATUS_ACTIVE) {
            return ['error' => 'Code not recognised or no longer active.', 'code' => 422];
        }

        if ($coupon->usage_limit !== null && $coupon->usage_count >= $coupon->usage_limit) {
            return ['error' => 'This code has reached its usage limit.', 'code' => 422];
        }

        // Influencer codes can't be used by the influencer themselves.
        if ($coupon->influencer_id !== null) {
            $coupon->loadMissing('influencer:id,owner_user_id');
            if ($coupon->influencer && (int) $coupon->influencer->owner_user_id === (int) $user->id) {
                return ['error' => 'You cannot use your own influencer code.', 'code' => 422];
            }
        }

        // Cart must contain the coupon's product.
        if ($coupon->product_id !== null) {
            $hasProduct = $cart->items->contains(fn ($item) => (int) $item->product_id === (int) $coupon->product_id);
            if (! $hasProduct) {
                return ['error' => 'This code only works on a specific product — add it to your cart first.', 'code' => 422];
            }
        }

        $cart->forceFill(['applied_coupon_code' => $code])->save();

        return ['coupon' => $coupon];
    }

    public function remove(Cart $cart): void
    {
        $cart->forceFill(['applied_coupon_code' => null])->save();
    }

    /**
     * Re-resolves the cart's applied code at snapshot time. Returns null if
     * the code is missing, archived, or no longer valid against the cart's
     * current contents — the cart's `applied_coupon_code` is auto-cleared
     * in that case to keep state honest.
     */
    public function resolveActive(Cart $cart): ?Coupon
    {
        if (! $cart->applied_coupon_code) {
            return null;
        }

        $coupon = $this->coupons->findByCode($cart->applied_coupon_code);
        if ($coupon === null || $coupon->status !== Coupon::STATUS_ACTIVE) {
            $this->remove($cart);

            return null;
        }
        if ($coupon->product_id !== null
            && ! $cart->items->contains(fn ($item) => (int) $item->product_id === (int) $coupon->product_id)) {
            $this->remove($cart);

            return null;
        }

        return $coupon;
    }
}
