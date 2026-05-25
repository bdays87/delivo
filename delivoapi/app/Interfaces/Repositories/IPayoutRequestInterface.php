<?php

namespace App\Interfaces\Repositories;

use App\Models\PayoutRequest;
use Illuminate\Database\Eloquent\Collection;

interface IPayoutRequestInterface extends IBaseInterface
{
    public function listForInfluencer(int $influencerId): Collection;

    public function listByStatus(?string $status = null): Collection;

    public function findForInfluencer(int $id, int $influencerId): ?PayoutRequest;

    public function findWithRelations(int $id): ?PayoutRequest;

    public function pendingForInfluencer(int $influencerId): Collection;
}
