<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\VendorDashboardService;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorDashboardController extends Controller
{
    public function __construct(
        private readonly VendorDashboardService $dashboard,
        private readonly VendorService $vendors,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        return ApiResponse::success(
            $this->dashboard->build($vendor),
            'Dashboard retrieved successfully.',
        );
    }
}
