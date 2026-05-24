<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\ICouponInterface;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Collection;

class CouponRepository extends BaseRepository implements ICouponInterface
{
    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?Coupon
    {
        return $this->model->where('code', $code)->first();
    }

    public function findForInfluencerProduct(int $influencerId, int $productId): ?Coupon
    {
        return $this->model
            ->where('influencer_id', $influencerId)
            ->where('product_id', $productId)
            ->first();
    }

    public function listForInfluencer(int $influencerId): Collection
    {
        return $this->model
            ->with(['product:id,name,slug,vendor_id', 'product.vendor:id,business_name'])
            ->where('influencer_id', $influencerId)
            ->latest()
            ->get();
    }

    public function codeExists(string $code): bool
    {
        return $this->model->where('code', $code)->exists();
    }
}
