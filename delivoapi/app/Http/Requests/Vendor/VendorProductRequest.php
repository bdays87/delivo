<?php

namespace App\Http\Requests\Vendor;

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
}
