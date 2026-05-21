<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IVendorInterface;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;

class VendorRepository extends BaseRepository implements IVendorInterface
{
    public function __construct(Vendor $model)
    {
        parent::__construct($model);
    }

    public function findByOwner(int $userId, array $relations = []): ?Vendor
    {
        return $this->model->with($relations)->where('owner_user_id', $userId)->first();
    }

    public function findBySlug(string $slug): ?Vendor
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function slugExists(string $slug): bool
    {
        return $this->model->where('slug', $slug)->exists();
    }

    public function createVendor(array $attributes): Vendor
    {
        return $this->model->create($attributes);
    }

    public function listByStatus(?string $status = null, array $relations = []): Collection
    {
        $query = $this->model->with($relations)->latest();

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function markStatus(Vendor $vendor, string $status, array $extra = []): bool
    {
        return $vendor->forceFill(array_merge(['status' => $status], $extra))->save();
    }

    /**
     * Active vendors with a count of their currently-listed products. Used by
     * the public "Shop by store" grid.
     */
    public function listPublicWithProductCounts(): Collection
    {
        return $this->model
            ->select(['id', 'business_name', 'slug', 'support_email', 'approved_at'])
            ->where('status', Vendor::STATUS_ACTIVE)
            ->withCount(['products' => function ($q) {
                $q->where('status', Product::STATUS_ACTIVE);
            }])
            ->orderBy('business_name')
            ->get();
    }
}
