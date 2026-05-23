<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminVehicleTypeRequest;
use App\Http\Responses\ApiResponse;
use App\Interfaces\Repositories\IVehicleTypeInterface;
use App\Models\VehicleType;
use Illuminate\Http\JsonResponse;

class AdminVehicleTypeController extends Controller
{
    public function __construct(private readonly IVehicleTypeInterface $repo) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success($this->repo->listAll(), 'Vehicle types retrieved.');
    }

    public function show(int $id): JsonResponse
    {
        $row = $this->repo->findById($id);
        if ($row === null) {
            return ApiResponse::notFound('Vehicle type not found.');
        }

        return ApiResponse::success($row, 'Vehicle type retrieved.');
    }

    public function store(AdminVehicleTypeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? VehicleType::STATUS_ACTIVE;
        $data['icon'] = $data['icon'] ?? 'lucide:truck';
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return ApiResponse::created($this->repo->create($data), 'Vehicle type created.');
    }

    public function update(AdminVehicleTypeRequest $request, int $id): JsonResponse
    {
        if ($this->repo->findById($id) === null) {
            return ApiResponse::notFound('Vehicle type not found.');
        }
        $this->repo->update($id, $request->validated());

        return ApiResponse::success([], 'Vehicle type updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        if ($this->repo->findById($id) === null) {
            return ApiResponse::notFound('Vehicle type not found.');
        }
        $this->repo->archive($id);

        return ApiResponse::success([], 'Vehicle type archived.');
    }
}
