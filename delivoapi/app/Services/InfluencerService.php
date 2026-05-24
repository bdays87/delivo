<?php

namespace App\Services;

use App\Interfaces\Repositories\IInfluencerInterface;
use App\Interfaces\Repositories\IUserInterface;
use App\Models\Influencer;
use App\Models\InfluencerSocialHandle;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InfluencerService
{
    public function __construct(
        private readonly IInfluencerInterface $influencers,
        private readonly IUserInterface $users,
    ) {}

    public function apply(User $owner, array $data): ?Influencer
    {
        if ($this->influencers->findByOwner($owner->id) !== null) {
            return null;
        }

        return DB::transaction(function () use ($owner, $data) {
            $influencer = $this->influencers->create([
                'owner_user_id' => $owner->id,
                'display_name' => $data['display_name'],
                'slug' => $data['slug'],
                'contact_email' => $data['contact_email'],
                'contact_phone' => $data['contact_phone'],
                'bio' => $data['bio'] ?? null,
                'niche' => $data['niche'] ?? null,
                'status' => Influencer::STATUS_PENDING,
            ]);

            $this->users->assignRole($owner, 'influencer');

            return $influencer;
        });
    }

    public function currentForUser(User $user): ?Influencer
    {
        return $this->influencers->findByOwner($user->id, ['socialHandles']);
    }

    /**
     * Add a social handle to the influencer's profile. Handles start
     * PENDING so admin can re-review when content changes substantially.
     */
    public function addHandle(Influencer $influencer, array $data): InfluencerSocialHandle
    {
        return InfluencerSocialHandle::query()->create([
            'influencer_id' => $influencer->id,
            'platform' => $data['platform'],
            'handle' => $data['handle'],
            'url' => $data['url'] ?? null,
            'followers' => $data['followers'] ?? null,
            'status' => InfluencerSocialHandle::STATUS_PENDING,
        ]);
    }

    public function findHandleForInfluencer(int $handleId, int $influencerId): ?InfluencerSocialHandle
    {
        return InfluencerSocialHandle::query()
            ->where('id', $handleId)
            ->where('influencer_id', $influencerId)
            ->first();
    }

    public function removeHandle(InfluencerSocialHandle $handle): bool
    {
        return (bool) $handle->delete();
    }
}
