<?php

namespace App\Http\Controllers\Api\Influencer;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Influencer;
use App\Models\Product;
use App\Services\InfluencerCouponService;
use App\Services\InfluencerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InfluencerProductController extends Controller
{
    public function __construct(
        private readonly InfluencerService $influencers,
        private readonly InfluencerCouponService $coupons,
    ) {}

    /**
     * Lists products an influencer can promote — ACTIVE products that pay
     * an influencer commission. Each row includes whether this influencer
     * already has a code generated for it.
     */
    public function index(Request $request): JsonResponse
    {
        $influencer = $this->influencers->currentForUser($request->user());
        if ($influencer === null || $influencer->status !== Influencer::STATUS_ACTIVE) {
            return ApiResponse::error('Your influencer account must be approved first.', 403);
        }

        $existingCodes = $this->coupons->listForInfluencer($influencer)
            ->pluck('code', 'product_id');

        $products = Product::query()
            ->with(['vendor:id,business_name,slug', 'category:id,name,slug', 'images'])
            ->where('status', Product::STATUS_ACTIVE)
            ->where('affiliate_influencer_pct', '>', 0)
            ->orderByDesc('approved_at')
            ->get()
            ->map(function (Product $p) use ($existingCodes) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'vendor' => $p->vendor ? ['id' => $p->vendor->id, 'business_name' => $p->vendor->business_name] : null,
                    'category' => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name] : null,
                    'affiliate_influencer_pct' => (float) $p->affiliate_influencer_pct,
                    'affiliate_buyer_discount_pct' => (float) $p->affiliate_buyer_discount_pct,
                    'primary_image' => $this->primaryImage($p),
                    'existing_code' => $existingCodes->get($p->id),
                ];
            });

        return ApiResponse::success($products, 'Promotable products retrieved successfully.');
    }

    /**
     * Idempotently generate (or fetch) an influencer's code for a product.
     */
    public function createCode(Request $request, int $productId): JsonResponse
    {
        $influencer = $this->influencers->currentForUser($request->user());
        if ($influencer === null) {
            return ApiResponse::notFound('No influencer application on file.');
        }

        $product = Product::query()->find($productId);
        if ($product === null) {
            return ApiResponse::notFound('Product not found.');
        }

        $result = $this->coupons->getOrCreateForProduct($influencer, $product);
        if (isset($result['error'])) {
            return ApiResponse::error($result['error'], $result['code'] ?? 422);
        }

        return ApiResponse::success($result['coupon'], 'Code ready to share.');
    }

    public function codes(Request $request): JsonResponse
    {
        $influencer = $this->influencers->currentForUser($request->user());
        if ($influencer === null) {
            return ApiResponse::notFound('No influencer application on file.');
        }

        return ApiResponse::success(
            $this->coupons->listForInfluencer($influencer),
            'Codes retrieved successfully.',
        );
    }

    private function primaryImage(Product $product): ?array
    {
        if (! $product->relationLoaded('images')) {
            return null;
        }
        $primary = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
        if ($primary === null) {
            return null;
        }

        return ['disk' => $primary->disk, 'path' => $primary->path];
    }
}
