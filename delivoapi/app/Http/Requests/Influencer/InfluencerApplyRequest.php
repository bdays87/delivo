<?php

namespace App\Http\Requests\Influencer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class InfluencerApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        $phone = (string) $this->input('contact_phone', '');
        $digits = preg_replace('/[^\d]/', '', $phone);
        if ($digits !== '') {
            if (str_starts_with($digits, '263')) {
                $merge['contact_phone'] = '+'.$digits;
            } elseif (str_starts_with($digits, '0')) {
                $merge['contact_phone'] = '+263'.substr($digits, 1);
            } else {
                $merge['contact_phone'] = '+263'.$digits;
            }
        }

        if (! $this->filled('slug') && $this->filled('display_name')) {
            $merge['slug'] = Str::slug((string) $this->input('display_name'));
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:160', 'regex:/^[a-z0-9-]+$/', 'unique:influencers,slug'],
            'contact_email' => ['required', 'email:rfc', 'max:255'],
            'contact_phone' => ['required', 'string', 'regex:/^\+2637\d{8}$/'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'niche' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug may only contain lowercase letters, numbers, and hyphens.',
            'contact_phone.regex' => 'Contact phone must be a Zimbabwean mobile number, e.g. 0772 000 000.',
        ];
    }
}
