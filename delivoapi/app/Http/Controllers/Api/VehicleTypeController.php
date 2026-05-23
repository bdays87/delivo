<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Interfaces\Repositories\IVehicleTypeInterface;
use Illuminate\Http\JsonResponse;

class VehicleTypeController extends Controller
{
    public function __construct(private readonly IVehicleTypeInterface $repo) {}

    /**
     * Public list of active vehicle types — used by the provider apply form.
     */
    public function listActive(): JsonResponse
    {
        return ApiResponse::success($this->repo->listActive(), 'Vehicle types retrieved.');
    }
}
