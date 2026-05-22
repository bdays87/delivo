<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CartItemRequest;
use App\Http\Requests\Customer\CartItemUpdateRequest;
use App\Http\Responses\ApiResponse;
use App\Interfaces\Repositories\ICartInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $service) {}

    public function show(Request $request): JsonResponse
    {
        $cart = $this->service->currentForUser($request->user());

        return ApiResponse::success($this->service->snapshot($cart), 'Cart retrieved successfully.');
    }

    public function addItem(CartItemRequest $request): JsonResponse
    {
        $variant = ProductVariant::with('product')->find($request->validated()['product_variant_id']);
        if ($variant === null) {
            return ApiResponse::notFound('Product variant not found.');
        }
        if ($variant->product === null || $variant->product->status !== Product::STATUS_ACTIVE) {
            return ApiResponse::error('This product is not available.', 422);
        }

        $this->service->addItem($request->user(), $variant, (int) $request->validated()['quantity']);
        $cart = $this->service->currentForUser($request->user());

        return ApiResponse::success($this->service->snapshot($cart), 'Item added to cart.');
    }

    public function updateItem(CartItemUpdateRequest $request, int $itemId): JsonResponse
    {
        $cartRepo = app(ICartInterface::class);
        $item = $cartRepo->findItemForUser($itemId, $request->user()->id);
        if ($item === null) {
            return ApiResponse::notFound('Cart item not found.');
        }

        $this->service->updateQuantity($item, (int) $request->validated()['quantity']);
        $cart = $this->service->currentForUser($request->user());

        return ApiResponse::success($this->service->snapshot($cart), 'Cart updated.');
    }

    public function removeItem(Request $request, int $itemId): JsonResponse
    {
        $cartRepo = app(ICartInterface::class);
        $item = $cartRepo->findItemForUser($itemId, $request->user()->id);
        if ($item === null) {
            return ApiResponse::notFound('Cart item not found.');
        }

        $this->service->removeItem($item);
        $cart = $this->service->currentForUser($request->user());

        return ApiResponse::success($this->service->snapshot($cart), 'Item removed from cart.');
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->service->currentForUser($request->user());
        $this->service->clear($cart);

        return ApiResponse::success($this->service->snapshot($cart->fresh()), 'Cart cleared.');
    }
}
