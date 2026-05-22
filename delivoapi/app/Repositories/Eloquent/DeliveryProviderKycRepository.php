<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IDeliveryProviderKycInterface;
use App\Models\DeliveryProviderKycDocument;

class DeliveryProviderKycRepository extends BaseRepository implements IDeliveryProviderKycInterface
{
    public function __construct(DeliveryProviderKycDocument $model)
    {
        parent::__construct($model);
    }

    public function createForProvider(int $providerId, array $data): DeliveryProviderKycDocument
    {
        return $this->model->create(array_merge($data, ['delivery_provider_id' => $providerId]));
    }

    public function findForProvider(int $documentId, int $providerId): ?DeliveryProviderKycDocument
    {
        return $this->model
            ->where('id', $documentId)
            ->where('delivery_provider_id', $providerId)
            ->first();
    }
}
