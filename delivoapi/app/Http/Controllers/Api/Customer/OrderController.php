<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $service) {}

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
}
