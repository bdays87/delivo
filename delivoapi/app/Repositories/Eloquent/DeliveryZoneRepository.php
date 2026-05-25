<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IDeliveryZoneInterface;
use App\Models\DeliveryZone;
use Illuminate\Database\Eloquent\Collection;

class DeliveryZoneRepository extends BaseRepository implements IDeliveryZoneInterface
{
    public function __construct(DeliveryZone $model)
    {
        parent::__construct($model);
    }

    public function listAll(): Collection
    {
        return $this->model
            ->orderBy('sort_order')
            ->orderBy('city')
            ->get();
    }

    public function listActive(): Collection
    {
        return $this->model
            ->where('status', DeliveryZone::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('city')
            ->get();
    }

    /**
     * Case-insensitive exact match on the city name. Returns null when no
     * active zone matches — caller falls back to the platform default fee.
     */
    public function findByCity(string $city): ?DeliveryZone
    {
        return $this->model
            ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim($city))])
            ->where('status', DeliveryZone::STATUS_ACTIVE)
            ->first();
    }

    public function listActiveByCity(string $city): Collection
    {
        return $this->model
            ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim($city))])
            ->where('status', DeliveryZone::STATUS_ACTIVE)
            ->orderBy('hub_name')
            ->get();
    }

    public function archive(int $id): bool
    {
        return $this->update($id, ['status' => DeliveryZone::STATUS_ARCHIVED]);
    }
}
