<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IVendorKycDocumentInterface;
use App\Models\VendorKycDocument;

class VendorKycDocumentRepository extends BaseRepository implements IVendorKycDocumentInterface
{
    public function __construct(VendorKycDocument $model)
    {
        parent::__construct($model);
    }

    public function createForVendor(int $vendorId, array $attributes): VendorKycDocument
    {
        return $this->model->create(array_merge(['vendor_id' => $vendorId], $attributes));
    }

    public function findForVendor(int $documentId, int $vendorId): ?VendorKycDocument
    {
        return $this->model->where('id', $documentId)->where('vendor_id', $vendorId)->first();
    }
}
