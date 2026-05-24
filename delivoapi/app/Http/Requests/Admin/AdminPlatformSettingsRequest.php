<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminPlatformSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_charge_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'service_charge_min_usd' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'affiliate_total_min_pct' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'affiliate_total_max_pct' => ['sometimes', 'numeric', 'min:0', 'max:100', 'gte:affiliate_total_min_pct'],
            'influencer_payout_fee_pct' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'influencer_payout_fee_min_usd' => ['sometimes', 'numeric', 'min:0', 'max:9999.99'],
        ];
    }
}
