<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'label' => ['nullable', 'string', 'max:40'],
            'recipient_name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:120'],
            'recipient_phone' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:40'],
            'city' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:80'],
            'suburb' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:120'],
            'street' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
