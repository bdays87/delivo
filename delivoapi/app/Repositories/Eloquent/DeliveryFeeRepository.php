<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IDeliveryFeeInterface;
use App\Models\DeliveryFee;
use Illuminate\Database\Eloquent\Collection;

class DeliveryFeeRepository extends BaseRepository implements IDeliveryFeeInterface
{
    public function __construct(DeliveryFee $model)
    {
        parent::__construct($model);
    }

    public function listAll(): Collection
    {
        return $this->model
            ->orderBy('min_km')
            ->orderBy('sort_order')
            ->get();
    }

    public function listActive(): Collection
    {
        return $this->model
            ->where('status', DeliveryFee::STATUS_ACTIVE)
            ->orderBy('min_km')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Resolves the first active band that covers the given distance. Returns
     * null when no band matches — admin must extend the table.
     */
    public function findBandForDistance(float $km): ?DeliveryFee
    {
        $bands = $this->listActive();
        foreach ($bands as $band) {
            if ($band->covers($km)) {
                return $band;
            }
        }

        return null;
    }

    public function archive(int $id): bool
    {
        return $this->update($id, ['status' => DeliveryFee::STATUS_ARCHIVED]);
    }
}
