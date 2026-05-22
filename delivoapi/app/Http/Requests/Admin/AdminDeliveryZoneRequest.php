<?php

namespace App\Http\Requests\Admin;

use App\Models\DeliveryZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminDeliveryZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $id = $this->route('id');

        return [
            'city' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:120',
                'unique:delivery_zones,city'.($id ? ",{$id}" : ''),
            ],
            'fee_usd' => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0', 'max:9999.99'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000'],
            'status' => [
                'sometimes',
                Rule::in([DeliveryZone::STATUS_ACTIVE, DeliveryZone::STATUS_ARCHIVED]),
            ],
        ];
    }
}
