<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminExchangeRateRequest;
use App\Http\Responses\ApiResponse;
use App\Services\ExchangeRateService;
use Illuminate\Http\JsonResponse;

class AdminExchangeRateController extends Controller
{
    public function __construct(private readonly ExchangeRateService $service) {}

    public function show(): JsonResponse
    {
        $rate = $this->service->usdToZwg();

        return ApiResponse::success(
            $rate ?? ['from_currency' => 'USD', 'to_currency' => 'ZWG', 'rate' => null],
            'Exchange rate retrieved.',
        );
    }

    public function update(AdminExchangeRateRequest $request): JsonResponse
    {
        $rate = $this->service->setUsdToZwg($request->user(), $request->validated()['rate']);

        return ApiResponse::success($rate, 'Exchange rate updated.');
    }
}
