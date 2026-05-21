<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $service) {}

    /**
     * Active catalogue categories for the storefront (no auth required).
     */
    public function listActive(): JsonResponse
    {
        return ApiResponse::success($this->service->listActive(), 'Categories retrieved successfully.');
    }
}
