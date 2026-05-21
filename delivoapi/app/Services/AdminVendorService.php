<?php

namespace App\Services;

use App\Interfaces\Repositories\IVendorInterface;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;

class AdminVendorService
{
    public function __construct(private readonly IVendorInterface $vendors) {}

    public function listByStatus(?string $status = null): Collection
    {
        return $this->vendors->listByStatus($status, ['owner', 'kycDocuments', 'payoutAccounts.mobileWallet']);
    }

    public function findById(int $id): ?Vendor
    {
        return $this->vendors->findById($id, ['*'], ['owner', 'kycDocuments.reviewer', 'payoutAccounts.mobileWallet']);
    }

    public function approve(Vendor $vendor): bool
    {
        return $this->vendors->markStatus($vendor, Vendor::STATUS_ACTIVE, [
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(Vendor $vendor, string $reason, User $reviewer): bool
    {
        return $this->vendors->markStatus($vendor, Vendor::STATUS_REJECTED, [
            'rejection_reason' => $reason,
            'rejected_at' => now(),
        ]);
    }

    public function suspend(Vendor $vendor, ?string $reason = null): bool
    {
        return $this->vendors->markStatus($vendor, Vendor::STATUS_SUSPENDED, [
            'suspended_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
