<?php

namespace App\Http\Requests\Provider;

use App\Models\DeliveryZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryProviderCoverageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_zone_ids' => ['present', 'array'],
            'delivery_zone_ids.*' => [
                'integer',
                Rule::exists('delivery_zones', 'id')->where('status', DeliveryZone::STATUS_ACTIVE),
            ],
        ];
    }
}
