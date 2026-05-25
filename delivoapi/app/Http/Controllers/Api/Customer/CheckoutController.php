<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CheckoutQuoteRequest;
use App\Http\Requests\Customer\CheckoutRequest;
use App\Http\Responses\ApiResponse;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $service) {}

    public function quote(CheckoutQuoteRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->service->quote(
            $request->user(),
            (int) $data['address_id'],
            (string) ($data['delivery_method'] ?? 'HOME_DELIVERY'),
        );

        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code'] ?? 422);
        }

        return ApiResponse::success($result['quote'], 'Quote calculated.');
    }

    public function place(CheckoutRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->service->place(
            $request->user(),
            (int) $data['address_id'],
            (int) $data['mobile_wallet_id'],
            (string) ($data['delivery_method'] ?? 'HOME_DELIVERY'),
        );

        if (isset($result['error'])) {
            return ApiResponse::error(
                $result['error'],
                $result['code'] ?? 422,
                ['lines' => $result['lines'] ?? []],
            );
        }

        return ApiResponse::created($result['order'], 'Order placed. Follow the payment instructions to complete checkout.');
    }
}
