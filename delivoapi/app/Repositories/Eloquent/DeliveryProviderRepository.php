<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IDeliveryProviderInterface;
use App\Models\DeliveryProvider;
use App\Models\DeliveryProviderRoute;
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

    public function syncVehicleTypes(DeliveryProvider $provider, array $vehicleTypeIds): void
    {
        $provider->vehicleTypes()->sync(array_values(array_unique($vehicleTypeIds)));
    }

    /**
     * Replaces the provider's routes wholesale — the apply/coverage form
     * re-sends the full route list on every save, so this keeps things
     * idempotent without diffing.
     *
     * @param  array<int, array{origin_city: string, destination_city: string, waypoints?: array}>  $routes
     */
    public function replaceRoutes(DeliveryProvider $provider, array $routes): void
    {
        $provider->routes()->delete();
        foreach ($routes as $r) {
            $provider->routes()->create([
                'origin_city' => $r['origin_city'],
                'destination_city' => $r['destination_city'],
                'waypoints' => $r['waypoints'] ?? [],
                'status' => DeliveryProviderRoute::STATUS_ACTIVE,
            ]);
        }
    }
}
