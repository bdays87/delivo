<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminDeliveryProviderRejectRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AdminDeliveryProviderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDeliveryProviderController extends Controller
{
    public function __construct(private readonly AdminDeliveryProviderService $service) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->listByStatus($request->query('status')),
            'Delivery providers retrieved successfully.',
        );
    }

    public function show(int $id): JsonResponse
    {
        $provider = $this->service->find($id);
        if ($provider === null) {
            return ApiResponse::notFound('Delivery provider not found.');
        }

        return ApiResponse::success($provider, 'Delivery provider retrieved successfully.');
    }

    public function approve(int $id): JsonResponse
    {
        $provider = $this->service->find($id);
        if ($provider === null) {
            return ApiResponse::notFound('Delivery provider not found.');
        }
        if (! $this->service->approve($provider)) {
            return ApiResponse::error('Only pending providers can be approved.', 409);
        }

        return ApiResponse::success([], 'Delivery provider approved.');
    }

    public function reject(AdminDeliveryProviderRejectRequest $request, int $id): JsonResponse
    {
        $provider = $this->service->find($id);
        if ($provider === null) {
            return ApiResponse::notFound('Delivery provider not found.');
        }
        if (! $this->service->reject($provider, $request->validated()['reason'])) {
            return ApiResponse::error('Only pending providers can be rejected.', 409);
        }

        return ApiResponse::success([], 'Delivery provider rejected.');
    }

    public function suspend(Request $request, int $id): JsonResponse
    {
        $provider = $this->service->find($id);
        if ($provider === null) {
            return ApiResponse::notFound('Delivery provider not found.');
        }
        if (! $this->service->suspend($provider, $request->input('reason'))) {
            return ApiResponse::error('Only active providers can be suspended.', 409);
        }

        return ApiResponse::success([], 'Delivery provider suspended.');
    }

    public function downloadKyc(int $provider, int $document): JsonResponse|StreamedResponse
    {
        $doc = $this->service->findKycDocumentForProvider($document, $provider);
        if ($doc === null) {
            return ApiResponse::notFound('KYC document not found.');
        }
        $stream = $this->service->streamKycDocument($doc);
        if ($stream === null) {
            return ApiResponse::notFound('KYC document file is missing.');
        }

        return $stream;
    }
}
