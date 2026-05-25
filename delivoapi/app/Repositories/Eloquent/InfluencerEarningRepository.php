<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IInfluencerEarningInterface;
use App\Models\InfluencerEarning;
use Illuminate\Database\Eloquent\Collection;

class InfluencerEarningRepository extends BaseRepository implements IInfluencerEarningInterface
{
    public function __construct(InfluencerEarning $model)
    {
        parent::__construct($model);
    }

    public function listForInfluencer(int $influencerId): Collection
    {
        return $this->model
            ->with(['order:id,order_number,delivered_at', 'orderItem:id,product_name_snapshot,quantity'])
            ->where('influencer_id', $influencerId)
            ->orderByDesc('id')
            ->get();
    }

    public function clearedTotal(int $influencerId): float
    {
        return (float) $this->model
            ->where('influencer_id', $influencerId)
            ->where('status', InfluencerEarning::STATUS_CLEARED)
            ->whereNull('payout_request_id')
            ->sum('amount_usd');
    }

    public function pendingTotal(int $influencerId): float
    {
        return (float) $this->model
            ->where('influencer_id', $influencerId)
            ->where('status', InfluencerEarning::STATUS_PENDING)
            ->sum('amount_usd');
    }

    public function paidTotal(int $influencerId): float
    {
        return (float) $this->model
            ->where('influencer_id', $influencerId)
            ->where('status', InfluencerEarning::STATUS_PAID)
            ->sum('amount_usd');
    }

    public function clearedForInfluencer(int $influencerId): Collection
    {
        return $this->model
            ->where('influencer_id', $influencerId)
            ->where('status', InfluencerEarning::STATUS_CLEARED)
            ->whereNull('payout_request_id')
            ->get();
    }

    public function lockClearedToPayout(int $influencerId, int $payoutRequestId): int
    {
        return $this->model
            ->where('influencer_id', $influencerId)
            ->where('status', InfluencerEarning::STATUS_CLEARED)
            ->whereNull('payout_request_id')
            ->update(['payout_request_id' => $payoutRequestId]);
    }

    public function unlockPayout(int $payoutRequestId): int
    {
        return $this->model
            ->where('payout_request_id', $payoutRequestId)
            ->update(['payout_request_id' => null]);
    }

    public function markPayoutPaid(int $payoutRequestId): int
    {
        return $this->model
            ->where('payout_request_id', $payoutRequestId)
            ->update([
                'status' => InfluencerEarning::STATUS_PAID,
                'paid_at' => now(),
            ]);
    }

    public function clearForOrder(int $orderId): int
    {
        return $this->model
            ->where('order_id', $orderId)
            ->where('status', InfluencerEarning::STATUS_PENDING)
            ->update([
                'status' => InfluencerEarning::STATUS_CLEARED,
                'cleared_at' => now(),
            ]);
    }
}
