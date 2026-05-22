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
            'default_delivery_fee_usd' => ['required', 'numeric', 'min:0', 'max:9999.99'],
        ];
    }
}
