<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Interfaces\Repositories\IDeliveryZoneInterface;
use Illuminate\Http\JsonResponse;

class CoverageAreaController extends Controller
{
    public function __construct(private readonly IDeliveryZoneInterface $zones) {}

    /**
     * Public list of active coverage cities. Powers the vendor-apply city
     * picker and the customer address-form picker — both must constrain
     * users to cities we actually deliver to.
     */
    public function listActive(): JsonResponse
    {
        $rows = $this->zones->listActive()->map(fn ($z) => [
            'id' => $z->id,
            'city' => $z->city,
            'fee_usd' => $z->fee_usd,
        ]);

        return ApiResponse::success($rows, 'Coverage areas retrieved successfully.');
    }
}
