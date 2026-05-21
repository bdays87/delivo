<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VendorRejectionRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AdminVendorService;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminVendorController extends Controller
{
    public function __construct(
        private readonly AdminVendorService $admin,
        private readonly VendorService $vendors,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $allowed = ['PENDING', 'ACTIVE', 'REJECTED', 'SUSPENDED'];

        if ($status !== null && ! in_array($status, $allowed, true)) {
            return ApiResponse::error('Unknown status filter.', 422);
        }

        return ApiResponse::success(
            $this->admin->listByStatus($status),
            'Vendors retrieved successfully.',
        );
    }

    public function show(int $id): JsonResponse
    {
        $vendor = $this->admin->findById($id);

        if ($vendor === null) {
            return ApiResponse::notFound('Vendor not found.');
        }

        return ApiResponse::success($vendor, 'Vendor retrieved successfully.');
    }

    public function approve(int $id): JsonResponse
    {
        $vendor = $this->admin->findById($id);

        if ($vendor === null) {
            return ApiResponse::notFound('Vendor not found.');
        }

        $this->admin->approve($vendor);

        return ApiResponse::success([], 'Vendor approved.');
    }

    public function reject(VendorRejectionRequest $request, int $id): JsonResponse
    {
        $vendor = $this->admin->findById($id);

        if ($vendor === null) {
            return ApiResponse::notFound('Vendor not found.');
        }

        $this->admin->reject($vendor, $request->validated()['reason'], $request->user());

        return ApiResponse::success([], 'Vendor rejected.');
    }

    public function suspend(Request $request, int $id): JsonResponse
    {
        $vendor = $this->admin->findById($id);

        if ($vendor === null) {
            return ApiResponse::notFound('Vendor not found.');
        }

        $reason = $request->input('reason');
        $this->admin->suspend($vendor, is_string($reason) ? $reason : null);

        return ApiResponse::success([], 'Vendor suspended.');
    }

    /**
     * Stream a KYC document. Token-mode admin clients can't attach the
     * bearer header to a plain <img>/<a> request, so this endpoint
     * authenticates via Sanctum and returns the file bytes directly.
     */
    public function downloadKyc(int $vendorId, int $documentId): StreamedResponse|JsonResponse
    {
        $vendor = $this->admin->findById($vendorId);

        if ($vendor === null) {
            return ApiResponse::notFound('Vendor not found.');
        }

        $document = $this->vendors->findKycDocumentForVendor($documentId, $vendor->id);

        if ($document === null) {
            return ApiResponse::notFound('Document not found.');
        }

        $stream = $this->vendors->streamKycDocument($document);

        if ($stream === null) {
            return ApiResponse::notFound('Document file is missing on disk.');
        }

        return $stream;
    }
}
