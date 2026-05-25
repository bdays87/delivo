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

    public function carts(Request $request): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());
        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        return ApiResponse::success(
            $this->orders->cartsForVendor($vendor),
            'Active carts retrieved successfully.',
        );
    }

    public function dropoffs(Request $request): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());
        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        return ApiResponse::success(
            $this->orders->shipmentsAwaitingDropoff($vendor),
            'Pending dropoffs retrieved successfully.',
        );
    }

    public function markDroppedOff(Request $request, int $shipmentId): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());
        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        $result = $this->orders->markDroppedOff($vendor, $shipmentId);
        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code']);
        }

        return ApiResponse::success($result['shipment'], 'Shipment marked dropped off.');
    }
}
