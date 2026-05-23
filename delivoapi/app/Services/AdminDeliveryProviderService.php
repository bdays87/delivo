<?php

namespace App\Services;

use App\Interfaces\Repositories\IDeliveryProviderInterface;
use App\Interfaces\Repositories\IDeliveryProviderKycInterface;
use App\Models\DeliveryProvider;
use App\Models\DeliveryProviderKycDocument;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDeliveryProviderService
{
    public function __construct(
        private readonly IDeliveryProviderInterface $providers,
        private readonly IDeliveryProviderKycInterface $kyc,
    ) {}

    public function listByStatus(?string $status = null): Collection
    {
        return $this->providers->listByStatus($status, ['owner:id,name,email,phone', 'coverageAreas', 'vehicleTypes']);
    }

    public function find(int $id): ?DeliveryProvider
    {
        return $this->providers->findById($id, ['*'], [
            'owner:id,name,email,phone',
            'kycDocuments',
            'coverageAreas',
            'vehicleTypes',
            'routes',
        ]);
    }

    public function approve(DeliveryProvider $provider): bool
    {
        if ($provider->status !== DeliveryProvider::STATUS_PENDING) {
            return false;
        }

        return $this->providers->markStatus($provider, DeliveryProvider::STATUS_ACTIVE, [
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(DeliveryProvider $provider, string $reason): bool
    {
        if ($provider->status !== DeliveryProvider::STATUS_PENDING) {
            return false;
        }

        return $this->providers->markStatus($provider, DeliveryProvider::STATUS_REJECTED, [
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function suspend(DeliveryProvider $provider, ?string $reason): bool
    {
        if ($provider->status !== DeliveryProvider::STATUS_ACTIVE) {
            return false;
        }

        return $this->providers->markStatus($provider, DeliveryProvider::STATUS_SUSPENDED, [
            'suspended_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function findKycDocumentForProvider(int $documentId, int $providerId): ?DeliveryProviderKycDocument
    {
        return $this->kyc->findForProvider($documentId, $providerId);
    }

    public function streamKycDocument(DeliveryProviderKycDocument $document): ?StreamedResponse
    {
        if (! Storage::disk($document->disk)->exists($document->path)) {
            return null;
        }

        return Storage::disk($document->disk)->response(
            $document->path,
            $document->original_filename,
            ['Content-Type' => $document->mime_type ?? 'application/octet-stream'],
        );
    }
}
