<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\ICartInterface;
use App\Models\Cart;
use App\Models\CartItem;

class CartRepository extends BaseRepository implements ICartInterface
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    public function findOrCreateForUser(int $userId): Cart
    {
        return $this->model->firstOrCreate(['user_id' => $userId]);
    }

    public function loadWithItems(Cart $cart): Cart
    {
        return $cart->load([
            'items.variant',
            'items.product:id,vendor_id,name,slug,status,category_id',
            'items.product.vendor:id,business_name,slug',
            'items.product.priceTiers',
            'items.product.images',
        ]);
    }

    public function findItem(int $cartId, int $variantId): ?CartItem
    {
        return CartItem::query()
            ->where('cart_id', $cartId)
            ->where('product_variant_id', $variantId)
            ->first();
    }

    public function findItemForUser(int $itemId, int $userId): ?CartItem
    {
        return CartItem::query()
            ->whereHas('cart', fn ($q) => $q->where('user_id', $userId))
            ->where('id', $itemId)
            ->first();
    }
}
