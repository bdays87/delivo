<?php

namespace App\Services;

use App\Interfaces\Repositories\IDeliveryProviderInterface;
use App\Interfaces\Repositories\IDeliveryProviderKycInterface;
use App\Interfaces\Repositories\IDeliveryZoneInterface;
use App\Interfaces\Repositories\IUserInterface;
use App\Models\DeliveryProvider;
use App\Models\DeliveryProviderKycDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DeliveryProviderService
{
    private const KYC_DISK = 'local';

    public function __construct(
        private readonly IDeliveryProviderInterface $providers,
        private readonly IDeliveryProviderKycInterface $kyc,
        private readonly IDeliveryZoneInterface $zones,
        private readonly IUserInterface $users,
    ) {}

    public function apply(User $owner, array $data): ?DeliveryProvider
    {
        if ($this->providers->findByOwner($owner->id) !== null) {
            return null;
        }

        return DB::transaction(function () use ($owner, $data) {
            $provider = $this->providers->create(array_merge(
                $data,
                [
                    'owner_user_id' => $owner->id,
                    'status' => DeliveryProvider::STATUS_PENDING,
                ],
            ));

            $this->users->assignRole($owner, 'delivery_provider');

            return $provider;
        });
    }

    public function currentForUser(User $user): ?DeliveryProvider
    {
        return $this->providers->findByOwner($user->id, ['kycDocuments', 'coverageAreas']);
    }

    public function attachKycDocument(DeliveryProvider $provider, string $type, UploadedFile $file): DeliveryProviderKycDocument
    {
        $directory = "delivery-provider-kyc/{$provider->id}";
        $path = $file->store($directory, self::KYC_DISK);

        return $this->kyc->createForProvider($provider->id, [
            'type' => $type,
            'original_filename' => $file->getClientOriginalName(),
            'disk' => self::KYC_DISK,
            'path' => $path,
            'size_bytes' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'status' => DeliveryProviderKycDocument::STATUS_PENDING,
        ]);
    }

    /**
     * Provider chooses which coverage cities they serve. Only ACTIVE
     * delivery_zones are allowed; the controller has already validated
     * the IDs against the active list.
     */
    public function syncCoverage(DeliveryProvider $provider, array $zoneIds): void
    {
        $this->providers->syncCoverage($provider, $zoneIds);
    }
}
