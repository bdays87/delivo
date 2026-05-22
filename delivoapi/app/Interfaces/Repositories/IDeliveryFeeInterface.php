<?php

namespace App\Interfaces\Repositories;

use App\Models\DeliveryFee;
use Illuminate\Database\Eloquent\Collection;

interface IDeliveryFeeInterface extends IBaseInterface
{
    public function listAll(): Collection;

    public function listActive(): Collection;

    public function findBandForDistance(float $km): ?DeliveryFee;

    public function archive(int $id): bool;
}
