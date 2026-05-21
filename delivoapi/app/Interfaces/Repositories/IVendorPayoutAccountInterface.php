<?php

namespace App\Interfaces\Repositories;

use App\Models\VendorPayoutAccount;
use Illuminate\Database\Eloquent\Collection;

interface IVendorPayoutAccountInterface extends IBaseInterface
{
    public function listForVendor(int $vendorId, array $relations = []): Collection;

    public function findForVendor(int $accountId, int $vendorId, array $relations = []): ?VendorPayoutAccount;

    public function createForVendor(int $vendorId, array $attributes): VendorPayoutAccount;

    public function clearPrimaryForVendor(int $vendorId, ?int $exceptId = null): void;

    public function archive(int $id): bool;
}
