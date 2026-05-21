<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class AdminCategoryController extends Controller
{
    public function __construct(private readonly CategoryService $service) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success($this->service->listAll(), 'Categories retrieved successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->service->findById($id);

        if ($category === null) {
            return ApiResponse::notFound('Category not found.');
        }

        return ApiResponse::success($category, 'Category retrieved successfully.');
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? Category::STATUS_ACTIVE;
        $data['icon'] = $data['icon'] ?? 'lucide:tag';

        return ApiResponse::created($this->service->create($data), 'Category created.');
    }

    public function update(CategoryRequest $request, int $id): JsonResponse
    {
        $updated = $this->service->update($id, $request->validated());

        if (! $updated) {
            return ApiResponse::notFound('Category not found.');
        }

        return ApiResponse::success([], 'Category updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $archived = $this->service->archive($id);

        if (! $archived) {
            return ApiResponse::notFound('Category not found.');
        }

        return ApiResponse::success([], 'Category archived.');
    }
}
