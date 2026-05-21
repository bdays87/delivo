<?php

namespace App\Interfaces\Repositories;

use App\Models\VendorKycDocument;

interface IVendorKycDocumentInterface extends IBaseInterface
{
    public function createForVendor(int $vendorId, array $attributes): VendorKycDocument;

    public function findForVendor(int $documentId, int $vendorId): ?VendorKycDocument;
}
