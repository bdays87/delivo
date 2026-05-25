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
            $this->orders->listForVendor(
                $vendor,
                $request->query('status'),
                $request->query('delivery_status'),
                $request->query('delivery_method'),
            ),
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

    public function dropoffHubs(Request $request): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());
        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        return ApiResponse::success(
            $this->orders->listDropoffHubs($vendor),
            'Dropoff hubs retrieved successfully.',
        );
    }

    public function initiateDropoff(Request $request, int $shipmentId): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());
        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        $data = $request->validate([
            'hub_id' => ['required', 'integer'],
        ]);

        $result = $this->orders->initiateDropoff($vendor, $shipmentId, (int) $data['hub_id']);
        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code']);
        }

        return ApiResponse::success($result['shipment'], 'Dropoff initiated.');
    }

    public function confirmSelfPickup(Request $request, string $orderNumber): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());
        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'min:4', 'max:10'],
        ]);

        $result = $this->orders->confirmSelfPickup($vendor, $orderNumber, (string) $data['code']);
        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code']);
        }

        return ApiResponse::success($result['order'], 'Pickup confirmed.');
    }
}
