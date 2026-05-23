<?php

namespace App\Http\Requests\Admin;

use App\Models\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminVehicleTypeRequest extends FormRequest
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
            'name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:80',
                'unique:vehicle_types,name'.($id ? ",{$id}" : ''),
            ],
            'icon' => ['sometimes', 'string', 'max:80'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000'],
            'status' => [
                'sometimes',
                Rule::in([VehicleType::STATUS_ACTIVE, VehicleType::STATUS_ARCHIVED]),
            ],
        ];
    }
}
