<?php

namespace App\Http\Requests\Customer;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'mobile_wallet_id' => ['required', 'integer', 'exists:mobile_wallets,id'],
            'delivery_method' => ['nullable', 'string', Rule::in([
                Order::METHOD_HOME_DELIVERY,
                Order::METHOD_SELF_PICKUP,
            ])],
        ];
    }
}
