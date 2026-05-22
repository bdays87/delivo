<?php

namespace App\Http\Requests\Provider;

use App\Models\DeliveryZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DeliveryProviderApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        $phone = (string) $this->input('support_phone', '');
        $digits = preg_replace('/[^\d]/', '', $phone);
        if ($digits !== '') {
            if (str_starts_with($digits, '263')) {
                $merge['support_phone'] = '+'.$digits;
            } elseif (str_starts_with($digits, '0')) {
                $merge['support_phone'] = '+263'.substr($digits, 1);
            } else {
                $merge['support_phone'] = '+263'.$digits;
            }
        }

        if (! $this->filled('slug') && $this->filled('business_name')) {
            $merge['slug'] = Str::slug((string) $this->input('business_name'));
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:160', 'regex:/^[a-z0-9-]+$/', 'unique:delivery_providers,slug'],
            'support_email' => ['required', 'email:rfc', 'max:255'],
            'support_phone' => ['required', 'string', 'regex:/^\+2637\d{8}$/'],
            'base_city' => [
                'required',
                'string',
                'max:120',
                Rule::exists('delivery_zones', 'city')->where('status', DeliveryZone::STATUS_ACTIVE),
            ],
            'vehicle_types' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug may only contain lowercase letters, numbers, and hyphens.',
            'support_phone.regex' => 'Support phone must be a Zimbabwean mobile number, e.g. 0772 000 000.',
            'base_city.exists' => "Delivo doesn't yet operate in this city. Pick a covered city.",
        ];
    }
}
