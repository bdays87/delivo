<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IVendorPayoutAccountInterface;
use App\Models\VendorPayoutAccount;
use Illuminate\Database\Eloquent\Collection;

class VendorPayoutAccountRepository extends BaseRepository implements IVendorPayoutAccountInterface
{
    public function __construct(VendorPayoutAccount $model)
    {
        parent::__construct($model);
    }

    public function listForVendor(int $vendorId, array $relations = []): Collection
    {
        return $this->model
            ->with($relations)
            ->where('vendor_id', $vendorId)
            ->orderByDesc('is_primary')
            ->latest()
            ->get();
    }

    public function findForVendor(int $accountId, int $vendorId, array $relations = []): ?VendorPayoutAccount
    {
        return $this->model
            ->with($relations)
            ->where('id', $accountId)
            ->where('vendor_id', $vendorId)
            ->first();
    }

    public function createForVendor(int $vendorId, array $attributes): VendorPayoutAccount
    {
        return $this->model->create(array_merge(['vendor_id' => $vendorId], $attributes));
    }

    public function clearPrimaryForVendor(int $vendorId, ?int $exceptId = null): void
    {
        $query = $this->model->where('vendor_id', $vendorId)->where('is_primary', true);
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }
        $query->update(['is_primary' => false]);
    }

    public function archive(int $id): bool
    {
        return $this->update($id, ['status' => VendorPayoutAccount::STATUS_ARCHIVED, 'is_primary' => false]);
    }
}
