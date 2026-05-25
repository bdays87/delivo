<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\VendorOrderService;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorOrderController extends Controller
{
    public function __construct(
        private readonly VendorService $vendors,
        private readonly VendorOrderService $orders,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());
        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        return ApiResponse::success(
            $this->orders->listForVendor($vendor, $request->query('status')),
            'Vendor orders retrieved successfully.',
        );
    }

    public function summary(Request $request): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());
        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        return ApiResponse::success(
            $this->orders->summary($vendor),
            'Vendor order summary retrieved successfully.',
        );
    }
}
