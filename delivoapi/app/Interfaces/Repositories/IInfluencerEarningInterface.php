<?php

namespace App\Interfaces\Repositories;

use App\Models\InfluencerEarning;
use Illuminate\Database\Eloquent\Collection;

interface IInfluencerEarningInterface extends IBaseInterface
{
    public function listForInfluencer(int $influencerId): Collection;

    public function clearedTotal(int $influencerId): float;

    public function pendingTotal(int $influencerId): float;

    public function paidTotal(int $influencerId): float;

    /** @return Collection<int, InfluencerEarning> */
    public function clearedForInfluencer(int $influencerId): Collection;

    public function lockClearedToPayout(int $influencerId, int $payoutRequestId): int;

    public function unlockPayout(int $payoutRequestId): int;

    public function markPayoutPaid(int $payoutRequestId): int;

    public function clearForOrder(int $orderId): int;
}
