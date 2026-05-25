<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ConfirmDeliveryRequest;
use App\Http\Responses\ApiResponse;
use App\Services\OrderService;
use App\Services\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $service,
        private readonly OrderStatusService $statusSvc,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->listForUser($request->user()),
            'Orders retrieved successfully.',
        );
    }

    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $order = $this->service->findForUser($request->user(), $orderNumber);
        if ($order === null) {
            return ApiResponse::notFound('Order not found.');
        }

        return ApiResponse::success($order, 'Order retrieved successfully.');
    }

    public function confirmDelivery(ConfirmDeliveryRequest $request, string $orderNumber): JsonResponse
    {
        $order = $this->service->findForUser($request->user(), $orderNumber);
        if ($order === null) {
            return ApiResponse::notFound('Order not found.');
        }

        $result = $this->statusSvc->confirmDelivery($order, (string) $request->input('code'));
        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code']);
        }

        return ApiResponse::success($result['order'], 'Delivery confirmed. Thank you.');
    }
}
