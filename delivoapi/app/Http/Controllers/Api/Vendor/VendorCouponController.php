<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\VendorCouponService;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorCouponController extends Controller
{
    public function __construct(
        private readonly VendorService $vendors,
        private readonly VendorCouponService $coupons,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());
        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        return ApiResponse::success(
            $this->coupons->listForVendor($vendor),
            'Vendor coupons retrieved successfully.',
        );
    }

    public function summary(Request $request): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());
        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        return ApiResponse::success(
            $this->coupons->summary($vendor),
            'Vendor coupon summary retrieved successfully.',
        );
    }
}
