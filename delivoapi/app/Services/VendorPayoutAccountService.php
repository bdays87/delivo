<?php

namespace App\Services;

use App\Interfaces\Repositories\IVendorPayoutAccountInterface;
use App\Models\Vendor;
use App\Models\VendorPayoutAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VendorPayoutAccountService
{
    public function __construct(private readonly IVendorPayoutAccountInterface $repository) {}

    public function listForVendor(Vendor $vendor): Collection
    {
        return $this->repository->listForVendor($vendor->id, ['mobileWallet']);
    }

    public function findForVendor(int $accountId, Vendor $vendor): ?VendorPayoutAccount
    {
        return $this->repository->findForVendor($accountId, $vendor->id, ['mobileWallet']);
    }

    /**
     * Create a payout account for the vendor. When `is_primary` is true,
     * any existing primary account is demoted first. If this is the very
     * first account, it's automatically made primary.
     */
    public function create(Vendor $vendor, array $data): VendorPayoutAccount
    {
        return DB::transaction(function () use ($vendor, $data) {
            $shouldBePrimary = ($data['is_primary'] ?? false) || ! $vendor->payoutAccounts()->exists();

            if ($shouldBePrimary) {
                $this->repository->clearPrimaryForVendor($vendor->id);
            }

            $payload = array_merge(
                $this->normaliseBranch($data),
                ['is_primary' => $shouldBePrimary, 'status' => VendorPayoutAccount::STATUS_ACTIVE],
            );

            return $this->repository->createForVendor($vendor->id, $payload);
        });
    }

    public function update(VendorPayoutAccount $account, array $data): bool
    {
        return DB::transaction(function () use ($account, $data) {
            if (($data['is_primary'] ?? false) === true) {
                $this->repository->clearPrimaryForVendor($account->vendor_id, $account->id);
            }

            return $this->repository->update($account->id, $this->normaliseBranch($data));
        });
    }

    public function archive(VendorPayoutAccount $account): bool
    {
        return $this->repository->archive($account->id);
    }

    public function setPrimary(VendorPayoutAccount $account): bool
    {
        return DB::transaction(function () use ($account) {
            $this->repository->clearPrimaryForVendor($account->vendor_id, $account->id);

            return $this->repository->update($account->id, ['is_primary' => true]);
        });
    }

    /**
     * Blank the irrelevant branch so a switch from one type to another
     * doesn't leave stale fields behind. The caller must already have
     * a normalised `type`.
     */
    private function normaliseBranch(array $data): array
    {
        if (! array_key_exists('type', $data)) {
            return $data;
        }

        if ($data['type'] === VendorPayoutAccount::TYPE_MOBILE_WALLET) {
            $data['bank_name'] = null;
            $data['bank_account_name'] = null;
            $data['bank_account_number'] = null;
            $data['bank_currency'] = null;
        } elseif ($data['type'] === VendorPayoutAccount::TYPE_BANK_TRANSFER) {
            $data['mobile_wallet_id'] = null;
            $data['mobile_wallet_msisdn'] = null;
        }

        return $data;
    }
}
