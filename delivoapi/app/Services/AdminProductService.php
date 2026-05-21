<?php

namespace App\Services;

use App\Interfaces\Repositories\IProductInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class AdminProductService
{
    public function __construct(private readonly IProductInterface $products) {}

    public function listByStatus(?string $status = null): Collection
    {
        return $this->products->listByStatus(
            $status,
            ['vendor:id,business_name,slug', 'category:id,name,slug', 'priceTiers', 'variants', 'images'],
        );
    }

    public function find(int $id): ?Product
    {
        return $this->products->findById($id, ['*'], [
            'vendor:id,business_name,slug,owner_user_id',
            'category:id,name,slug',
            'priceTiers',
            'variants',
            'images',
        ]);
    }

    public function approve(Product $product): bool
    {
        if (! in_array($product->status, [Product::STATUS_PENDING, Product::STATUS_REJECTED], true)) {
            return false;
        }

        return $this->products->update($product->id, [
            'status' => Product::STATUS_ACTIVE,
            'approved_at' => now(),
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);
    }

    public function reject(Product $product, string $reason): bool
    {
        if ($product->status !== Product::STATUS_PENDING) {
            return false;
        }

        return $this->products->update($product->id, [
            'status' => Product::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Take an ACTIVE product down — moves it back to PENDING with a reason
     * so the vendor must address it before going live again.
     */
    public function takedown(Product $product, string $reason): bool
    {
        if ($product->status !== Product::STATUS_ACTIVE) {
            return false;
        }

        return $this->products->update($product->id, [
            'status' => Product::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
