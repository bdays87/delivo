<?php

namespace App\Http\Requests\Admin;

use App\Models\MobileWallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MobileWalletRequest extends FormRequest
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
            'code' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:30',
                'regex:/^[A-Z0-9_]+$/',
                'unique:mobile_wallets,code'.($id ? ",{$id}" : ''),
            ],
            'name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:100',
                'unique:mobile_wallets,name'.($id ? ",{$id}" : ''),
            ],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000'],
            'status' => [
                'sometimes',
                Rule::in([MobileWallet::STATUS_ACTIVE, MobileWallet::STATUS_ARCHIVED]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Code may only contain uppercase letters, numbers and underscores.',
        ];
    }
}
