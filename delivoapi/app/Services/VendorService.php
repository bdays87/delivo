<?php

namespace App\Services;

use App\Interfaces\Repositories\IUserInterface;
use App\Interfaces\Repositories\IVendorInterface;
use App\Interfaces\Repositories\IVendorKycDocumentInterface;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorKycDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VendorService
{
    private const KYC_DISK = 'local';

    public function __construct(
        private readonly IVendorInterface $vendors,
        private readonly IVendorKycDocumentInterface $kyc,
        private readonly IUserInterface $users,
    ) {}

    /**
     * Submit a vendor application for the given owner. Returns the created
     * vendor or null when the user already owns a vendor record.
     */
    public function apply(User $owner, array $data): ?Vendor
    {
        if ($this->vendors->findByOwner($owner->id) !== null) {
            return null;
        }

        return DB::transaction(function () use ($owner, $data) {
            $vendor = $this->vendors->createVendor(array_merge(
                $data,
                [
                    'owner_user_id' => $owner->id,
                    'status' => Vendor::STATUS_PENDING,
                ],
            ));

            $this->users->assignRole($owner, 'vendor');

            return $vendor;
        });
    }

    public function currentForUser(User $user): ?Vendor
    {
        return $this->vendors->findByOwner($user->id, ['kycDocuments', 'payoutAccounts.mobileWallet']);
    }

    /**
     * Persist an uploaded KYC document on the local disk and create the
     * accompanying database record.
     */
    public function attachKycDocument(Vendor $vendor, string $type, UploadedFile $file): VendorKycDocument
    {
        $directory = "vendor-kyc/{$vendor->id}";
        $path = $file->store($directory, self::KYC_DISK);

        return $this->kyc->createForVendor($vendor->id, [
            'type' => $type,
            'original_filename' => $file->getClientOriginalName(),
            'disk' => self::KYC_DISK,
            'path' => $path,
            'size_bytes' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'status' => VendorKycDocument::STATUS_PENDING,
        ]);
    }

    public function findKycDocumentForVendor(int $documentId, int $vendorId): ?VendorKycDocument
    {
        return $this->kyc->findForVendor($documentId, $vendorId);
    }

    /**
     * Stream a stored KYC document. Returns null if the disk path is missing
     * — caller decides the 404 response.
     */
    public function streamKycDocument(VendorKycDocument $document): ?StreamedResponse
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
