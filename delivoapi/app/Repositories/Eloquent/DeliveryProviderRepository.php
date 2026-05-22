<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IDeliveryProviderInterface;
use App\Models\DeliveryProvider;
use Illuminate\Database\Eloquent\Collection;

class DeliveryProviderRepository extends BaseRepository implements IDeliveryProviderInterface
{
    public function __construct(DeliveryProvider $model)
    {
        parent::__construct($model);
    }

    public function findByOwner(int $userId, array $relations = []): ?DeliveryProvider
    {
        return $this->model->with($relations)->where('owner_user_id', $userId)->first();
    }

    public function findBySlug(string $slug): ?DeliveryProvider
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function slugExists(string $slug): bool
    {
        return $this->model->where('slug', $slug)->exists();
    }

    public function listByStatus(?string $status, array $relations = []): Collection
    {
        $query = $this->model->with($relations)->latest();
        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function markStatus(DeliveryProvider $provider, string $status, array $extra = []): bool
    {
        return $provider->forceFill(array_merge(['status' => $status], $extra))->save();
    }

    public function syncCoverage(DeliveryProvider $provider, array $zoneIds): void
    {
        $provider->coverageAreas()->sync(array_values(array_unique($zoneIds)));
    }
}
