<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IPayoutRequestInterface;
use App\Models\PayoutRequest;
use Illuminate\Database\Eloquent\Collection;

class PayoutRequestRepository extends BaseRepository implements IPayoutRequestInterface
{
    public function __construct(PayoutRequest $model)
    {
        parent::__construct($model);
    }

    public function listForInfluencer(int $influencerId): Collection
    {
        return $this->model
            ->where('influencer_id', $influencerId)
            ->latest('id')
            ->get();
    }

    public function listByStatus(?string $status = null): Collection
    {
        $query = $this->model->with([
            'influencer:id,display_name,slug,contact_email',
            'processedBy:id,name',
        ]);
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query->latest('id')->get();
    }

    public function findForInfluencer(int $id, int $influencerId): ?PayoutRequest
    {
        return $this->model
            ->where('id', $id)
            ->where('influencer_id', $influencerId)
            ->first();
    }

    public function findWithRelations(int $id): ?PayoutRequest
    {
        return $this->model
            ->with([
                'influencer:id,display_name,slug,contact_email,contact_phone',
                'processedBy:id,name',
            ])
            ->find($id);
    }

    public function pendingForInfluencer(int $influencerId): Collection
    {
        return $this->model
            ->where('influencer_id', $influencerId)
            ->whereIn('status', [PayoutRequest::STATUS_PENDING, PayoutRequest::STATUS_APPROVED])
            ->get();
    }
}
