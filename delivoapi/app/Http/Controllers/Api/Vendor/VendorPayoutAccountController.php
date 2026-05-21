<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorPayoutAccountRequest;
use App\Http\Responses\ApiResponse;
use App\Services\VendorPayoutAccountService;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorPayoutAccountController extends Controller
{
    public function __construct(
        private readonly VendorService $vendors,
        private readonly VendorPayoutAccountService $accounts,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('Submit your vendor application before managing payout accounts.');
        }

        return ApiResponse::success(
            $this->accounts->listForVendor($vendor),
            'Payout accounts retrieved successfully.',
        );
    }

    public function store(VendorPayoutAccountRequest $request): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('Submit your vendor application before adding payout accounts.');
        }

        $account = $this->accounts->create($vendor, $request->validated());

        return ApiResponse::created($account->load('mobileWallet'), 'Payout account added.');
    }

    public function update(VendorPayoutAccountRequest $request, int $id): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('Vendor not found.');
        }

        $account = $this->accounts->findForVendor($id, $vendor);

        if ($account === null) {
            return ApiResponse::notFound('Payout account not found.');
        }

        $this->accounts->update($account, $request->validated());

        return ApiResponse::success([], 'Payout account updated.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('Vendor not found.');
        }

        $account = $this->accounts->findForVendor($id, $vendor);

        if ($account === null) {
            return ApiResponse::notFound('Payout account not found.');
        }

        $this->accounts->archive($account);

        return ApiResponse::success([], 'Payout account archived.');
    }

    public function setPrimary(Request $request, int $id): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('Vendor not found.');
        }

        $account = $this->accounts->findForVendor($id, $vendor);

        if ($account === null) {
            return ApiResponse::notFound('Payout account not found.');
        }

        $this->accounts->setPrimary($account);

        return ApiResponse::success([], 'Primary payout account updated.');
    }
}
