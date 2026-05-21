<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MobileWalletRequest;
use App\Http\Responses\ApiResponse;
use App\Models\MobileWallet;
use App\Services\MobileWalletService;
use Illuminate\Http\JsonResponse;

class AdminMobileWalletController extends Controller
{
    public function __construct(private readonly MobileWalletService $service) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success($this->service->listAll(), 'Mobile wallets retrieved successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $wallet = $this->service->findById($id);

        if ($wallet === null) {
            return ApiResponse::notFound('Mobile wallet not found.');
        }

        return ApiResponse::success($wallet, 'Mobile wallet retrieved successfully.');
    }

    public function store(MobileWalletRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? MobileWallet::STATUS_ACTIVE;

        return ApiResponse::created($this->service->create($data), 'Mobile wallet created.');
    }

    public function update(MobileWalletRequest $request, int $id): JsonResponse
    {
        $updated = $this->service->update($id, $request->validated());

        if (! $updated) {
            return ApiResponse::notFound('Mobile wallet not found.');
        }

        return ApiResponse::success([], 'Mobile wallet updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $archived = $this->service->archive($id);

        if (! $archived) {
            return ApiResponse::notFound('Mobile wallet not found.');
        }

        return ApiResponse::success([], 'Mobile wallet archived.');
    }
}
