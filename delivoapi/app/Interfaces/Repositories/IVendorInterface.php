<?php

namespace App\Interfaces\Repositories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;

interface IVendorInterface extends IBaseInterface
{
    public function findByOwner(int $userId, array $relations = []): ?Vendor;

    public function findBySlug(string $slug): ?Vendor;

    public function slugExists(string $slug): bool;

    public function createVendor(array $attributes): Vendor;

    public function listByStatus(?string $status = null, array $relations = []): Collection;

    public function markStatus(Vendor $vendor, string $status, array $extra = []): bool;

    public function listPublicWithProductCounts(): Collection;
}
