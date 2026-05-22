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
            'hub_name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:150'],
            'hub_address' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'hub_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'hub_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000'],
            'status' => [
                'sometimes',
                Rule::in([DeliveryZone::STATUS_ACTIVE, DeliveryZone::STATUS_ARCHIVED]),
            ],
        ];
    }
}
