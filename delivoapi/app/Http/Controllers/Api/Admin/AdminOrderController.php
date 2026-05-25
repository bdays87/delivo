<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\AdminOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function __construct(private readonly AdminOrderService $service) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->listByStatus(
                $request->query('status'),
                $request->query('delivery_status'),
            ),
            'Orders retrieved successfully.',
        );
    }

    public function show(string $orderNumber): JsonResponse
    {
        $order = $this->service->find($orderNumber);
        if ($order === null) {
            return ApiResponse::notFound('Order not found.');
        }

        return ApiResponse::success($order, 'Order retrieved successfully.');
    }

    public function confirmPayment(string $orderNumber): JsonResponse
    {
        $order = $this->service->find($orderNumber);
        if ($order === null) {
            return ApiResponse::notFound('Order not found.');
        }

        $result = $this->service->confirmPayment($order);
        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code']);
        }

        return ApiResponse::success($result['order'], 'Payment confirmed.');
    }

    public function markDroppedOff(string $orderNumber): JsonResponse
    {
        $order = $this->service->find($orderNumber);
        if ($order === null) {
            return ApiResponse::notFound('Order not found.');
        }

        $result = $this->service->markDroppedOff($order);
        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code']);
        }

        return ApiResponse::success($result['order'], 'Order marked dropped off.');
    }

    public function markDelivered(string $orderNumber): JsonResponse
    {
        $order = $this->service->find($orderNumber);
        if ($order === null) {
            return ApiResponse::notFound('Order not found.');
        }

        $result = $this->service->markDelivered($order);
        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code']);
        }

        return ApiResponse::success($result['order'], 'Order marked delivered.');
    }
}
