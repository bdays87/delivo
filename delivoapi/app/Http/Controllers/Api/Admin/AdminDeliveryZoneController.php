<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminDeliveryZoneRequest;
use App\Http\Responses\ApiResponse;
use App\Interfaces\Repositories\IDeliveryZoneInterface;
use App\Models\DeliveryZone;
use Illuminate\Http\JsonResponse;

class AdminDeliveryZoneController extends Controller
{
    public function __construct(private readonly IDeliveryZoneInterface $repo) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success($this->repo->listAll(), 'Delivery zones retrieved.');
    }

    public function show(int $id): JsonResponse
    {
        $zone = $this->repo->findById($id);
        if ($zone === null) {
            return ApiResponse::notFound('Delivery zone not found.');
        }

        return ApiResponse::success($zone, 'Delivery zone retrieved.');
    }

    public function store(AdminDeliveryZoneRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? DeliveryZone::STATUS_ACTIVE;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return ApiResponse::created($this->repo->create($data), 'Delivery zone created.');
    }

    public function update(AdminDeliveryZoneRequest $request, int $id): JsonResponse
    {
        if ($this->repo->findById($id) === null) {
            return ApiResponse::notFound('Delivery zone not found.');
        }

        $this->repo->update($id, $request->validated());

        return ApiResponse::success([], 'Delivery zone updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        if ($this->repo->findById($id) === null) {
            return ApiResponse::notFound('Delivery zone not found.');
        }

        $this->repo->archive($id);

        return ApiResponse::success([], 'Delivery zone archived.');
    }
}
