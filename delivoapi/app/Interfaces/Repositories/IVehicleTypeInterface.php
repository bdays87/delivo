<?php

namespace App\Interfaces\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface IVehicleTypeInterface extends IBaseInterface
{
    public function listAll(): Collection;

    public function listActive(): Collection;

    public function archive(int $id): bool;
}
