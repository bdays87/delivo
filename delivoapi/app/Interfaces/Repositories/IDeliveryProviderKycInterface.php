<?php

namespace App\Interfaces\Repositories;

use App\Models\DeliveryProviderKycDocument;

interface IDeliveryProviderKycInterface extends IBaseInterface
{
    public function createForProvider(int $providerId, array $data): DeliveryProviderKycDocument;

    public function findForProvider(int $documentId, int $providerId): ?DeliveryProviderKycDocument;
}
