<?php

namespace App\Interfaces\Repositories;

use App\Models\DeliveryProvider;
use Illuminate\Database\Eloquent\Collection;

interface IDeliveryProviderInterface extends IBaseInterface
{
    public function findByOwner(int $userId, array $relations = []): ?DeliveryProvider;

    public function findBySlug(string $slug): ?DeliveryProvider;

    public function slugExists(string $slug): bool;

    public function listByStatus(?string $status, array $relations = []): Collection;

    public function markStatus(DeliveryProvider $provider, string $status, array $extra = []): bool;

    public function syncCoverage(DeliveryProvider $provider, array $zoneIds): void;
}
