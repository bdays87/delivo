<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\DeliveryProvider;
use App\Services\DeliveryProviderService;
use App\Services\ProviderShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderShipmentController extends Controller
{
    public function __construct(
        private readonly DeliveryProviderService $providers,
        private readonly ProviderShipmentService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $provider = $this->requireActiveProvider($request);
        if ($provider instanceof JsonResponse) {
            return $provider;
        }

        return ApiResponse::success(
            $this->service->listForProvider($provider, $request->query('status')),
            'Shipments retrieved successfully.',
        );
    }

    public function show(Request $request, int $shipmentId): JsonResponse
    {
        $provider = $this->requireActiveProvider($request);
        if ($provider instanceof JsonResponse) {
            return $provider;
        }

        $shipment = $this->service->findForProvider($provider, $shipmentId);
        if ($shipment === null) {
            return ApiResponse::notFound('Shipment not found.');
        }

        return ApiResponse::success($shipment, 'Shipment retrieved successfully.');
    }

    public function pickup(Request $request, int $shipmentId): JsonResponse
    {
        return $this->transition($request, $shipmentId, 'pickup', 'Only assigned shipments can be picked up.');
    }

    public function dispatch(Request $request, int $shipmentId): JsonResponse
    {
        return $this->transition($request, $shipmentId, 'dispatchShipment', 'Only picked-up shipments can be dispatched.');
    }

    public function deliver(Request $request, int $shipmentId): JsonResponse
    {
        return $this->transition($request, $shipmentId, 'deliver', 'Only out-for-delivery shipments can be delivered.');
    }

    private function transition(Request $request, int $shipmentId, string $method, string $errorMessage): JsonResponse
    {
        $provider = $this->requireActiveProvider($request);
        if ($provider instanceof JsonResponse) {
            return $provider;
        }

        $shipment = $this->service->findForProvider($provider, $shipmentId);
        if ($shipment === null) {
            return ApiResponse::notFound('Shipment not found.');
        }

        if (! $this->service->{$method}($shipment)) {
            return ApiResponse::error($errorMessage, 409);
        }

        return ApiResponse::success($shipment->fresh(['order', 'vendor', 'hub']), 'Shipment updated.');
    }

    private function requireActiveProvider(Request $request): DeliveryProvider|JsonResponse
    {
        $provider = $this->providers->currentForUser($request->user());
        if ($provider === null) {
            return ApiResponse::error('Submit and get your delivery provider application approved first.', 403);
        }
        if ($provider->status !== DeliveryProvider::STATUS_ACTIVE) {
            return ApiResponse::error('Your delivery provider account must be approved before handling shipments.', 403);
        }

        return $provider;
    }
}
