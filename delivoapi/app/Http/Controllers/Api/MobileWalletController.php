<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\MobileWalletService;
use Illuminate\Http\JsonResponse;

class MobileWalletController extends Controller
{
    public function __construct(private readonly MobileWalletService $service) {}

    /**
     * Returns active mobile wallets for use as a lookup (e.g. vendor apply
     * form). Auth-only — no admin gate.
     */
    public function listActive(): JsonResponse
    {
        return ApiResponse::success(
            $this->service->listActive(),
            'Mobile wallets retrieved successfully.',
        );
    }
}
