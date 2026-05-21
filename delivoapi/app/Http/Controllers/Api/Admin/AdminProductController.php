<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminProductRejectRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AdminProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function __construct(private readonly AdminProductService $service) {}

    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');

        return ApiResponse::success(
            $this->service->listByStatus($status),
            'Products retrieved successfully.',
        );
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->service->find($id);

        if ($product === null) {
            return ApiResponse::notFound('Product not found.');
        }

        return ApiResponse::success($product, 'Product retrieved successfully.');
    }

    public function approve(int $id): JsonResponse
    {
        $product = $this->service->find($id);

        if ($product === null) {
            return ApiResponse::notFound('Product not found.');
        }

        if (! $this->service->approve($product)) {
            return ApiResponse::error('Only pending or rejected products can be approved.', 409);
        }

        return ApiResponse::success([], 'Product approved.');
    }

    public function reject(AdminProductRejectRequest $request, int $id): JsonResponse
    {
        $product = $this->service->find($id);

        if ($product === null) {
            return ApiResponse::notFound('Product not found.');
        }

        if (! $this->service->reject($product, $request->validated()['reason'])) {
            return ApiResponse::error('Only pending products can be rejected.', 409);
        }

        return ApiResponse::success([], 'Product rejected.');
    }

    public function takedown(AdminProductRejectRequest $request, int $id): JsonResponse
    {
        $product = $this->service->find($id);

        if ($product === null) {
            return ApiResponse::notFound('Product not found.');
        }

        if (! $this->service->takedown($product, $request->validated()['reason'])) {
            return ApiResponse::error('Only active products can be taken down.', 409);
        }

        return ApiResponse::success([], 'Product taken down.');
    }
}
