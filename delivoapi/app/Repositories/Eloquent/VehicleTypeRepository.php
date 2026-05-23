<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IVehicleTypeInterface;
use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Collection;

class VehicleTypeRepository extends BaseRepository implements IVehicleTypeInterface
{
    public function __construct(VehicleType $model)
    {
        parent::__construct($model);
    }

    public function listAll(): Collection
    {
        return $this->model->orderBy('sort_order')->orderBy('name')->get();
    }

    public function listActive(): Collection
    {
        return $this->model
            ->where('status', VehicleType::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function archive(int $id): bool
    {
        return $this->update($id, ['status' => VehicleType::STATUS_ARCHIVED]);
    }
}
