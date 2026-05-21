<?php

namespace App\Interfaces\Repositories;

use App\Models\Cart;
use App\Models\CartItem;

interface ICartInterface extends IBaseInterface
{
    public function findOrCreateForUser(int $userId): Cart;

    public function loadWithItems(Cart $cart): Cart;

    public function findItem(int $cartId, int $variantId): ?CartItem;

    public function findItemForUser(int $itemId, int $userId): ?CartItem;
}
