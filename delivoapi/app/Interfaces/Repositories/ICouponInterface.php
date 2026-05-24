<?php

namespace App\Interfaces\Repositories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Collection;

interface ICouponInterface extends IBaseInterface
{
    public function findByCode(string $code): ?Coupon;

    public function findForInfluencerProduct(int $influencerId, int $productId): ?Coupon;

    public function listForInfluencer(int $influencerId): Collection;

    public function codeExists(string $code): bool;
}
