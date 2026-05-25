<?php

namespace App\Interfaces\Repositories;

use App\Models\DeliveryZone;
use Illuminate\Database\Eloquent\Collection;

interface IDeliveryZoneInterface extends IBaseInterface
{
    public function listAll(): Collection;

    public function listActive(): Collection;

    public function findByCity(string $city): ?DeliveryZone;

    public function listActiveByCity(string $city): Collection;

    public function archive(int $id): bool;
}
