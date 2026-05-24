<?php

namespace App\Services;

use App\Interfaces\Repositories\IInfluencerInterface;
use App\Models\Influencer;
use App\Models\InfluencerSocialHandle;
use Illuminate\Database\Eloquent\Collection;

class AdminInfluencerService
{
    public function __construct(private readonly IInfluencerInterface $influencers) {}

    public function listByStatus(?string $status = null): Collection
    {
        return $this->influencers->listByStatus($status, ['owner:id,name,email', 'socialHandles']);
    }

    public function find(int $id): ?Influencer
    {
        return $this->influencers->findById($id, ['*'], ['owner:id,name,email,phone', 'socialHandles']);
    }

    public function approve(Influencer $influencer): bool
    {
        if ($influencer->status !== Influencer::STATUS_PENDING) {
            return false;
        }

        // Auto-approve any PENDING handles when the influencer is approved.
        InfluencerSocialHandle::query()
            ->where('influencer_id', $influencer->id)
            ->where('status', InfluencerSocialHandle::STATUS_PENDING)
            ->update(['status' => InfluencerSocialHandle::STATUS_APPROVED]);

        return $this->influencers->markStatus($influencer, Influencer::STATUS_ACTIVE, [
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(Influencer $influencer, string $reason): bool
    {
        if ($influencer->status !== Influencer::STATUS_PENDING) {
            return false;
        }

        return $this->influencers->markStatus($influencer, Influencer::STATUS_REJECTED, [
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function suspend(Influencer $influencer, ?string $reason): bool
    {
        if ($influencer->status !== Influencer::STATUS_ACTIVE) {
            return false;
        }

        return $this->influencers->markStatus($influencer, Influencer::STATUS_SUSPENDED, [
            'suspended_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Mark a single social handle approved/rejected after content review.
     */
    public function setHandleStatus(InfluencerSocialHandle $handle, string $status): bool
    {
        if (! in_array($status, [
            InfluencerSocialHandle::STATUS_APPROVED,
            InfluencerSocialHandle::STATUS_REJECTED,
            InfluencerSocialHandle::STATUS_PENDING,
        ], true)) {
            return false;
        }

        return $handle->forceFill(['status' => $status])->save();
    }
}
