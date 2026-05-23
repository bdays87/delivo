<?php

namespace App\Services;

use App\Interfaces\Repositories\IDeliveryProviderInterface;
use App\Interfaces\Repositories\IDeliveryProviderKycInterface;
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
        private readonly IUserInterface $users,
    ) {}

    /**
     * Apply as a delivery provider. Captures business info, route_type
     * (INTRA_CITY or INTER_CITY), vehicle types and (for inter-city
     * fleets) the optional offers_intra_city flag.
     */
    public function apply(User $owner, array $data): ?DeliveryProvider
    {
        if ($this->providers->findByOwner($owner->id) !== null) {
            return null;
        }

        return DB::transaction(function () use ($owner, $data) {
            $vehicleTypeIds = $data['vehicle_type_ids'] ?? [];
            $providerData = [
                'business_name' => $data['business_name'],
                'slug' => $data['slug'],
                'support_email' => $data['support_email'],
                'support_phone' => $data['support_phone'],
                'base_city' => $data['base_city'],
                'route_type' => $data['route_type'],
                'offers_intra_city' => (bool) ($data['offers_intra_city'] ?? false),
                'owner_user_id' => $owner->id,
                'status' => DeliveryProvider::STATUS_PENDING,
            ];

            $provider = $this->providers->create($providerData);
            $this->providers->syncVehicleTypes($provider, $vehicleTypeIds);

            $this->users->assignRole($owner, 'delivery_provider');

            return $provider;
        });
    }

    public function currentForUser(User $user): ?DeliveryProvider
    {
        return $this->providers->findByOwner($user->id, [
            'kycDocuments',
            'coverageAreas',
            'vehicleTypes',
            'routes',
        ]);
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

    public function syncCoverage(DeliveryProvider $provider, array $zoneIds): void
    {
        $this->providers->syncCoverage($provider, $zoneIds);
    }

    public function syncVehicleTypes(DeliveryProvider $provider, array $vehicleTypeIds): void
    {
        $this->providers->syncVehicleTypes($provider, $vehicleTypeIds);
    }

    public function replaceRoutes(DeliveryProvider $provider, array $routes): void
    {
        $this->providers->replaceRoutes($provider, $routes);
    }

    public function setOffersIntraCity(DeliveryProvider $provider, bool $offers): void
    {
        $provider->forceFill(['offers_intra_city' => $offers])->save();
    }
}
