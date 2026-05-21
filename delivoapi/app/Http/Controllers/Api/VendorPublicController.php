<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Interfaces\Repositories\IVendorInterface;
use Illuminate\Http\JsonResponse;

class VendorPublicController extends Controller
{
    public function __construct(private readonly IVendorInterface $vendors) {}

    /**
     * Active vendors with their ACTIVE-product counts. Powers the "Shop by
     * store" landing grid and the future /vendors browse page.
     */
    public function listActive(): JsonResponse
    {
        return ApiResponse::success(
            $this->vendors->listPublicWithProductCounts(),
            'Vendors retrieved successfully.',
        );
    }
}
