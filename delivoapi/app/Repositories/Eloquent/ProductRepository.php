<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IProductInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository implements IProductInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function listForVendor(int $vendorId, ?string $status, array $relations = []): Collection
    {
        $query = $this->model->with($relations)->where('vendor_id', $vendorId)->latest();

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function findForVendor(int $id, int $vendorId, array $relations = []): ?Product
    {
        return $this->model->with($relations)
            ->where('vendor_id', $vendorId)
            ->where('id', $id)
            ->first();
    }

    public function listByStatus(?string $status, array $relations = []): Collection
    {
        $query = $this->model->with($relations)->latest();

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function paginatePublic(?int $categoryId, ?string $search, int $perPage = 24): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['vendor:id,business_name,slug', 'category:id,name,slug', 'images', 'priceTiers', 'variants'])
            ->where('status', Product::STATUS_ACTIVE)
            ->latest('approved_at');

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function findActiveBySlug(string $slug, array $relations = []): ?Product
    {
        return $this->model->with($relations)
            ->where('slug', $slug)
            ->where('status', Product::STATUS_ACTIVE)
            ->first();
    }

    public function slugExists(string $slug): bool
    {
        return $this->model->where('slug', $slug)->exists();
    }
}
