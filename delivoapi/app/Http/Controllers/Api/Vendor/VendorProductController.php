<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorProductImageRequest;
use App\Http\Requests\Vendor\VendorProductRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\VendorProductService;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorProductController extends Controller
{
    public function __construct(
        private readonly VendorProductService $service,
        private readonly VendorService $vendors,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        $status = $request->query('status');

        return ApiResponse::success(
            $this->service->listForVendor($vendor, $status),
            'Products retrieved successfully.',
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        $product = $this->service->findForVendor($vendor, $id);

        if ($product === null) {
            return ApiResponse::notFound('Product not found.');
        }

        return ApiResponse::success($product, 'Product retrieved successfully.');
    }

    public function store(VendorProductRequest $request): JsonResponse
    {
        $vendor = $this->requireActiveVendor($request);

        if ($vendor instanceof JsonResponse) {
            return $vendor;
        }

        $product = $this->service->create($vendor, $request->validated());

        return ApiResponse::created($product, 'Product submitted for review.');
    }

    public function update(VendorProductRequest $request, int $id): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        $product = $this->service->findForVendor($vendor, $id);

        if ($product === null) {
            return ApiResponse::notFound('Product not found.');
        }

        if ($product->status === Product::STATUS_ARCHIVED) {
            return ApiResponse::error('Archived products cannot be edited.', 409);
        }

        $updated = $this->service->update($product, $request->validated());

        return ApiResponse::success($updated, 'Product updated.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        $product = $this->service->findForVendor($vendor, $id);

        if ($product === null) {
            return ApiResponse::notFound('Product not found.');
        }

        $this->service->archive($product);

        return ApiResponse::success([], 'Product archived.');
    }

    public function resubmit(Request $request, int $id): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        $product = $this->service->findForVendor($vendor, $id);

        if ($product === null) {
            return ApiResponse::notFound('Product not found.');
        }

        if (! $this->service->resubmit($product)) {
            return ApiResponse::error('Only rejected products can be resubmitted.', 409);
        }

        return ApiResponse::success([], 'Product resubmitted for review.');
    }

    public function uploadImage(VendorProductImageRequest $request, int $id): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        $product = $this->service->findForVendor($vendor, $id);

        if ($product === null) {
            return ApiResponse::notFound('Product not found.');
        }

        $image = $this->service->attachImage($product, $request->file('image'));

        return ApiResponse::created($image, 'Image uploaded.');
    }

    public function setPrimaryImage(Request $request, int $id, int $imageId): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        $product = $this->service->findForVendor($vendor, $id);

        if ($product === null) {
            return ApiResponse::notFound('Product not found.');
        }

        if (! $this->service->setPrimaryImage($product, $imageId)) {
            return ApiResponse::notFound('Image not found.');
        }

        return ApiResponse::success([], 'Primary image updated.');
    }

    public function deleteImage(Request $request, int $id, int $imageId): JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('No vendor profile on file.');
        }

        $product = $this->service->findForVendor($vendor, $id);

        if ($product === null) {
            return ApiResponse::notFound('Product not found.');
        }

        if (! $this->service->deleteImage($product, $imageId)) {
            return ApiResponse::notFound('Image not found.');
        }

        return ApiResponse::success([], 'Image deleted.');
    }

    /**
     * Returns the current user's vendor only when it is ACTIVE — products
     * can only be created/edited by approved vendors.
     */
    private function requireActiveVendor(Request $request): Vendor|JsonResponse
    {
        $vendor = $this->vendors->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::error('Submit and get your vendor application approved first.', 403);
        }

        if ($vendor->status !== Vendor::STATUS_ACTIVE) {
            return ApiResponse::error('Your vendor account must be approved before listing products.', 403);
        }

        return $vendor;
    }
}
