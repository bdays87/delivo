<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Interfaces\Repositories\IProductInterface;
use App\Services\ExchangeRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly IProductInterface $products,
        private readonly ExchangeRateService $rates,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $categoryId = $request->query('category_id');
        $search = $request->query('q');
        $perPage = min(60, max(1, (int) $request->query('per_page', 24)));

        $page = $this->products->paginatePublic(
            $categoryId !== null ? (int) $categoryId : null,
            $search,
            $perPage,
        );

        return ApiResponse::success([
            'items' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
            'exchange_rate' => $this->rates->usdToZwg(),
        ], 'Products retrieved successfully.');
    }

    public function show(string $slug): JsonResponse
    {
        $product = $this->products->findActiveBySlug($slug, [
            'vendor:id,business_name,slug',
            'category:id,name,slug',
            'priceTiers',
            'variants',
            'images',
        ]);

        if ($product === null) {
            return ApiResponse::notFound('Product not found.');
        }

        return ApiResponse::success([
            'product' => $product,
            'exchange_rate' => $this->rates->usdToZwg(),
        ], 'Product retrieved successfully.');
    }
}
