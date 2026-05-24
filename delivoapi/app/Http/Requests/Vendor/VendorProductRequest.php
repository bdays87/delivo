<?php

namespace App\Http\Requests\Vendor;

use App\Models\PlatformSettings;
use Illuminate\Foundation\Http\FormRequest;

class VendorProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'sku' => ['nullable', 'string', 'max:80'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:9999.999'],

            // Affiliate split. Both default to 0 (no affiliate program). Per-field
            // bounds are 0..100; cross-field cap against the platform's
            // affiliate_total_max_pct is enforced in `withValidator`.
            'affiliate_influencer_pct' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'affiliate_buyer_discount_pct' => ['sometimes', 'numeric', 'min:0', 'max:100'],

            'price_tiers' => ['required', 'array', 'min:1'],
            'price_tiers.*.min_qty' => ['required', 'integer', 'min:1'],
            'price_tiers.*.unit_price' => ['required', 'numeric', 'min:0.01'],

            'variants' => ['required', 'array', 'min:1'],
            'variants.*.color' => ['nullable', 'string', 'max:60'],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0'],
            'variants.*.sku' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function messages(): array
    {
        return [
            'price_tiers.min' => 'Add at least one price tier.',
            'variants.min' => 'Add at least one variant (use a single row if no color choice).',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $influencer = (float) $this->input('affiliate_influencer_pct', 0);
            $buyer = (float) $this->input('affiliate_buyer_discount_pct', 0);
            $total = $influencer + $buyer;
            if ($total <= 0) {
                return; // no affiliate program declared
            }

            $settings = PlatformSettings::query()->first();
            $min = (float) ($settings?->affiliate_total_min_pct ?? 0);
            $max = (float) ($settings?->affiliate_total_max_pct ?? 50);

            if ($total < $min) {
                $v->errors()->add('affiliate_influencer_pct', "Total affiliate commission must be at least {$min}%.");
            }
            if ($total > $max) {
                $v->errors()->add('affiliate_influencer_pct', "Total affiliate commission cannot exceed {$max}%.");
            }
        });
    }
}
