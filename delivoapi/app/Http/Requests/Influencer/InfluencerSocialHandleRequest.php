<?php

namespace App\Http\Requests\Influencer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InfluencerSocialHandleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => ['required', Rule::in(['INSTAGRAM', 'TIKTOK', 'X', 'YOUTUBE', 'FACEBOOK', 'OTHER'])],
            'handle' => ['required', 'string', 'max:120'],
            'url' => ['nullable', 'url', 'max:255'],
            'followers' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
        ];
    }
}
