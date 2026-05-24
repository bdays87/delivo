<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IInfluencerInterface;
use App\Models\Influencer;
use Illuminate\Database\Eloquent\Collection;

class InfluencerRepository extends BaseRepository implements IInfluencerInterface
{
    public function __construct(Influencer $model)
    {
        parent::__construct($model);
    }

    public function findByOwner(int $userId, array $relations = []): ?Influencer
    {
        return $this->model->with($relations)->where('owner_user_id', $userId)->first();
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

    public function markStatus(Influencer $influencer, string $status, array $extra = []): bool
    {
        return $influencer->forceFill(array_merge(['status' => $status], $extra))->save();
    }
}
