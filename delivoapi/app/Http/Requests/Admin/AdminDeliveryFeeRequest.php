<?php

namespace App\Http\Requests\Admin;

use App\Models\DeliveryFee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminDeliveryFeeRequest extends FormRequest
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
            'min_km' => [
                $isUpdate ? 'sometimes' : 'required',
                'integer',
                'min:0',
                'max:99999',
                'unique:delivery_fees,min_km'.($id ? ",{$id}" : ''),
            ],
            'max_km' => ['nullable', 'integer', 'min:0', 'max:99999', 'gte:min_km'],
            'fee_usd' => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0', 'max:9999.99'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000'],
            'status' => [
                'sometimes',
                Rule::in([DeliveryFee::STATUS_ACTIVE, DeliveryFee::STATUS_ARCHIVED]),
            ],
        ];
    }
}
