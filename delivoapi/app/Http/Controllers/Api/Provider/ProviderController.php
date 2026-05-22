<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\DeliveryProviderApplyRequest;
use App\Http\Requests\Provider\DeliveryProviderCoverageRequest;
use App\Http\Requests\Provider\DeliveryProviderKycUploadRequest;
use App\Http\Responses\ApiResponse;
use App\Services\DeliveryProviderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function __construct(private readonly DeliveryProviderService $service) {}

    public function apply(DeliveryProviderApplyRequest $request): JsonResponse
    {
        $provider = $this->service->apply($request->user(), $request->validated());

        if ($provider === null) {
            return ApiResponse::error('You already have a delivery provider application on file.', 409);
        }

        return ApiResponse::created($provider->load('kycDocuments'), 'Application submitted.');
    }

    public function currentProvider(Request $request): JsonResponse
    {
        $provider = $this->service->currentForUser($request->user());
        if ($provider === null) {
            return ApiResponse::notFound('No delivery provider application on file.');
        }

        return ApiResponse::success($provider, 'Provider retrieved successfully.');
    }

    public function uploadKyc(DeliveryProviderKycUploadRequest $request): JsonResponse
    {
        $provider = $this->service->currentForUser($request->user());
        if ($provider === null) {
            return ApiResponse::notFound('Submit your application before uploading documents.');
        }

        $document = $this->service->attachKycDocument(
            $provider,
            $request->validated()['type'],
            $request->file('document'),
        );

        return ApiResponse::created($document, 'Document uploaded.');
    }

    public function syncCoverage(DeliveryProviderCoverageRequest $request): JsonResponse
    {
        $provider = $this->service->currentForUser($request->user());
        if ($provider === null) {
            return ApiResponse::notFound('No delivery provider application on file.');
        }

        $this->service->syncCoverage($provider, $request->validated()['delivery_zone_ids'] ?? []);

        return ApiResponse::success(
            $provider->fresh(['coverageAreas']),
            'Coverage updated.',
        );
    }
}
