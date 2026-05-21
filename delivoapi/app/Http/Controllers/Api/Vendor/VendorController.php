<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorApplyRequest;
use App\Http\Requests\Vendor\VendorKycUploadRequest;
use App\Http\Responses\ApiResponse;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct(private readonly VendorService $service) {}

    public function apply(VendorApplyRequest $request): JsonResponse
    {
        $vendor = $this->service->apply($request->user(), $request->validated());

        if ($vendor === null) {
            return ApiResponse::error('You already have a vendor application on file.', 409);
        }

        return ApiResponse::created($vendor->load('kycDocuments'), 'Application submitted.');
    }

    public function currentVendor(Request $request): JsonResponse
    {
        $vendor = $this->service->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('No vendor application on file.');
        }

        return ApiResponse::success($vendor, 'Vendor retrieved successfully.');
    }

    public function uploadKyc(VendorKycUploadRequest $request): JsonResponse
    {
        $vendor = $this->service->currentForUser($request->user());

        if ($vendor === null) {
            return ApiResponse::notFound('Submit your vendor application before uploading documents.');
        }

        $document = $this->service->attachKycDocument(
            $vendor,
            $request->validated()['type'],
            $request->file('document'),
        );

        return ApiResponse::created($document, 'Document uploaded.');
    }
}
