<?php

namespace App\Services;

use App\Interfaces\Repositories\IProductImageInterface;
use App\Interfaces\Repositories\IProductInterface;
use App\Interfaces\Repositories\IProductPriceTierInterface;
use App\Interfaces\Repositories\IProductVariantInterface;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VendorProductService
{
    private const IMAGE_DISK = 'public';

    public function __construct(
        private readonly IProductInterface $products,
        private readonly IProductImageInterface $images,
        private readonly IProductPriceTierInterface $tiers,
        private readonly IProductVariantInterface $variants,
    ) {}

    public function listForVendor(Vendor $vendor, ?string $status = null): Collection
    {
        return $this->products->listForVendor(
            $vendor->id,
            $status,
            ['category:id,name,slug', 'priceTiers', 'variants', 'images'],
        );
    }

    public function findForVendor(Vendor $vendor, int $productId): ?Product
    {
        return $this->products->findForVendor(
            $productId,
            $vendor->id,
            ['category:id,name,slug', 'priceTiers', 'variants', 'images'],
        );
    }

    /**
     * Create a new product under this vendor in PENDING state with its
     * tiers and variants synced atomically.
     */
    public function create(Vendor $vendor, array $data): Product
    {
        return DB::transaction(function () use ($vendor, $data) {
            $product = $this->products->create([
                'vendor_id' => $vendor->id,
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $this->generateSlug($data['name']),
                'description' => $data['description'] ?? null,
                'sku' => $data['sku'] ?? null,
                'weight_kg' => $data['weight_kg'] ?? null,
                'affiliate_influencer_pct' => $data['affiliate_influencer_pct'] ?? 0,
                'affiliate_buyer_discount_pct' => $data['affiliate_buyer_discount_pct'] ?? 0,
                'status' => Product::STATUS_PENDING,
                'submitted_at' => now(),
            ]);

            $this->tiers->syncForProduct($product->id, $data['price_tiers']);
            $this->variants->syncForProduct($product->id, $data['variants']);

            return $product->fresh(['category:id,name,slug', 'priceTiers', 'variants', 'images']);
        });
    }

    /**
     * Update an existing vendor-owned product. Any edit on an ACTIVE
     * product flips it back to PENDING (locked v1 rule).
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $attrs = [
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'sku' => $data['sku'] ?? null,
                'weight_kg' => $data['weight_kg'] ?? null,
                'affiliate_influencer_pct' => $data['affiliate_influencer_pct'] ?? 0,
                'affiliate_buyer_discount_pct' => $data['affiliate_buyer_discount_pct'] ?? 0,
            ];

            if ($product->status === Product::STATUS_ACTIVE) {
                $attrs['status'] = Product::STATUS_PENDING;
                $attrs['submitted_at'] = now();
            } elseif ($product->status === Product::STATUS_REJECTED) {
                $attrs['status'] = Product::STATUS_PENDING;
                $attrs['submitted_at'] = now();
                $attrs['rejection_reason'] = null;
                $attrs['rejected_at'] = null;
            }

            $this->products->update($product->id, $attrs);

            $this->tiers->syncForProduct($product->id, $data['price_tiers']);
            $this->variants->syncForProduct($product->id, $data['variants']);

            return $product->fresh(['category:id,name,slug', 'priceTiers', 'variants', 'images']);
        });
    }

    public function archive(Product $product): bool
    {
        return $this->products->update($product->id, ['status' => Product::STATUS_ARCHIVED]);
    }

    /**
     * Store an uploaded image on the public disk. The first image attached
     * to a product is marked primary automatically.
     */
    public function attachImage(Product $product, UploadedFile $file): ProductImage
    {
        $directory = "products/{$product->id}";
        $path = $file->store($directory, self::IMAGE_DISK);

        $hasExisting = $product->images()->exists();

        return $this->images->createForProduct($product, [
            'disk' => self::IMAGE_DISK,
            'path' => $path,
            'sort_order' => $product->images()->count(),
            'is_primary' => ! $hasExisting,
        ]);
    }

    public function setPrimaryImage(Product $product, int $imageId): bool
    {
        $image = $this->images->findForProduct($imageId, $product->id);

        if ($image === null) {
            return false;
        }

        return DB::transaction(function () use ($image, $product) {
            $this->images->clearPrimaryForProduct($product->id);
            $image->forceFill(['is_primary' => true])->save();

            return true;
        });
    }

    public function deleteImage(Product $product, int $imageId): bool
    {
        $image = $this->images->findForProduct($imageId, $product->id);

        if ($image === null) {
            return false;
        }

        Storage::disk($image->disk)->delete($image->path);
        $wasPrimary = $image->is_primary;
        $image->delete();

        if ($wasPrimary) {
            $next = $product->images()->orderBy('sort_order')->first();
            if ($next !== null) {
                $next->forceFill(['is_primary' => true])->save();
            }
        }

        return true;
    }

    /**
     * Resubmit a REJECTED product without other edits.
     */
    public function resubmit(Product $product): bool
    {
        if ($product->status !== Product::STATUS_REJECTED) {
            return false;
        }

        return $this->products->update($product->id, [
            'status' => Product::STATUS_PENDING,
            'submitted_at' => now(),
            'rejection_reason' => null,
            'rejected_at' => null,
        ]);
    }

    private function generateSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base.'-'.Str::lower(Str::random(6));

        while ($this->products->slugExists($slug)) {
            $slug = $base.'-'.Str::lower(Str::random(6));
        }

        return $slug;
    }
}
