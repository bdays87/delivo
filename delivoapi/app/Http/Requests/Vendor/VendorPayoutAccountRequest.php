<?php

namespace App\Http\Requests\Vendor;

use App\Models\VendorPayoutAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorPayoutAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $msisdn = (string) $this->input('mobile_wallet_msisdn', '');
        $digits = preg_replace('/[^\d]/', '', $msisdn);
        if ($digits !== '') {
            $merge = [];
            if (str_starts_with($digits, '263')) {
                $merge['mobile_wallet_msisdn'] = '+'.$digits;
            } elseif (str_starts_with($digits, '0')) {
                $merge['mobile_wallet_msisdn'] = '+263'.substr($digits, 1);
            } else {
                $merge['mobile_wallet_msisdn'] = '+263'.$digits;
            }
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $type = $this->input('type');

        return [
            'type' => [
                $isUpdate ? 'sometimes' : 'required',
                Rule::in([VendorPayoutAccount::TYPE_MOBILE_WALLET, VendorPayoutAccount::TYPE_BANK_TRANSFER]),
            ],
            'label' => ['nullable', 'string', 'max:100'],
            'is_primary' => ['sometimes', 'boolean'],

            // Mobile-wallet branch
            'mobile_wallet_id' => [
                'nullable',
                Rule::requiredIf(fn () => $type === VendorPayoutAccount::TYPE_MOBILE_WALLET),
                'integer',
                'exists:mobile_wallets,id',
            ],
            'mobile_wallet_msisdn' => [
                'nullable',
                Rule::requiredIf(fn () => $type === VendorPayoutAccount::TYPE_MOBILE_WALLET),
                'string',
                'regex:/^\+2637\d{8}$/',
            ],

            // Bank-transfer branch
            'bank_name' => [
                'nullable',
                Rule::requiredIf(fn () => $type === VendorPayoutAccount::TYPE_BANK_TRANSFER),
                'string',
                'max:120',
            ],
            'bank_account_name' => [
                'nullable',
                Rule::requiredIf(fn () => $type === VendorPayoutAccount::TYPE_BANK_TRANSFER),
                'string',
                'max:150',
            ],
            'bank_account_number' => [
                'nullable',
                Rule::requiredIf(fn () => $type === VendorPayoutAccount::TYPE_BANK_TRANSFER),
                'string',
                'max:64',
            ],
            'bank_currency' => [
                'nullable',
                Rule::requiredIf(fn () => $type === VendorPayoutAccount::TYPE_BANK_TRANSFER),
                'string',
                Rule::in(['USD', 'ZWG']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile_wallet_msisdn.regex' => 'Wallet number must be a Zimbabwean mobile number, e.g. 0772 000 000.',
            'bank_currency.in' => 'Bank currency must be USD or ZWG.',
        ];
    }
}
