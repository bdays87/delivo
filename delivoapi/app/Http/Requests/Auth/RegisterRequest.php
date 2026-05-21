<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalises ZW phone numbers into the canonical +263xxxxxxxxx form
     * before validation runs. Accepts 077…, 0772…, 263…, +263…, etc.
     */
    protected function prepareForValidation(): void
    {
        $phone = (string) $this->input('phone', '');
        $digits = preg_replace('/[^\d]/', '', $phone);

        if ($digits === '') {
            return;
        }

        if (str_starts_with($digits, '263')) {
            $normalised = '+'.$digits;
        } elseif (str_starts_with($digits, '0')) {
            $normalised = '+263'.substr($digits, 1);
        } else {
            $normalised = '+263'.$digits;
        }

        $this->merge(['phone' => $normalised]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^\+2637\d{8}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Phone must be a Zimbabwean mobile number (e.g. 0772 000 000).',
        ];
    }
}
