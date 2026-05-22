<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminDeliveryFeeRequest;
use App\Http\Responses\ApiResponse;
use App\Interfaces\Repositories\IDeliveryFeeInterface;
use App\Models\DeliveryFee;
use Illuminate\Http\JsonResponse;

class AdminDeliveryFeeController extends Controller
{
    public function __construct(private readonly IDeliveryFeeInterface $repo) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success($this->repo->listAll(), 'Delivery fees retrieved.');
    }

    public function show(int $id): JsonResponse
    {
        $band = $this->repo->findById($id);
        if ($band === null) {
            return ApiResponse::notFound('Delivery fee not found.');
        }

        return ApiResponse::success($band, 'Delivery fee retrieved.');
    }

    public function store(AdminDeliveryFeeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? DeliveryFee::STATUS_ACTIVE;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return ApiResponse::created($this->repo->create($data), 'Delivery fee created.');
    }

    public function update(AdminDeliveryFeeRequest $request, int $id): JsonResponse
    {
        if ($this->repo->findById($id) === null) {
            return ApiResponse::notFound('Delivery fee not found.');
        }

        $this->repo->update($id, $request->validated());

        return ApiResponse::success([], 'Delivery fee updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        if ($this->repo->findById($id) === null) {
            return ApiResponse::notFound('Delivery fee not found.');
        }

        $this->repo->archive($id);

        return ApiResponse::success([], 'Delivery fee archived.');
    }
}
