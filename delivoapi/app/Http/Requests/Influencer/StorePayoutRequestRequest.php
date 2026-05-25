<?php

namespace App\Http\Requests\Influencer;

use App\Models\PayoutRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePayoutRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => ['required', 'string', Rule::in([
                PayoutRequest::METHOD_MOBILE_MONEY,
                PayoutRequest::METHOD_BANK_TRANSFER,
            ])],
            'destination_label' => ['nullable', 'string', 'max:120'],
            'destination_account' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
