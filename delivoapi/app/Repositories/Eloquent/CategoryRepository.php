<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\ICategoryInterface;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository implements ICategoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function listActive(): Collection
    {
        return $this->model
            ->with('parent:id,name,slug')
            ->withCount(['products' => function ($q) {
                $q->where('status', Product::STATUS_ACTIVE);
            }])
            ->where('status', Category::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function listAll(): Collection
    {
        return $this->model
            ->with('parent:id,name,slug')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function archive(int $id): bool
    {
        return $this->update($id, ['status' => Category::STATUS_ARCHIVED]);
    }
}
