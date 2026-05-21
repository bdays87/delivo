<?php

namespace App\Interfaces\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface IProductInterface extends IBaseInterface
{
    public function listForVendor(int $vendorId, ?string $status, array $relations = []): Collection;

    public function findForVendor(int $id, int $vendorId, array $relations = []): ?Product;

    public function listByStatus(?string $status, array $relations = []): Collection;

    public function paginatePublic(?int $categoryId, ?string $search, int $perPage = 24): LengthAwarePaginator;

    public function findActiveBySlug(string $slug, array $relations = []): ?Product;

    public function slugExists(string $slug): bool;
}
