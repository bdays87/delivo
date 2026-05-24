<?php

namespace App\Interfaces\Repositories;

use App\Models\Influencer;
use Illuminate\Database\Eloquent\Collection;

interface IInfluencerInterface extends IBaseInterface
{
    public function findByOwner(int $userId, array $relations = []): ?Influencer;

    public function slugExists(string $slug): bool;

    public function listByStatus(?string $status, array $relations = []): Collection;

    public function markStatus(Influencer $influencer, string $status, array $extra = []): bool;
}
