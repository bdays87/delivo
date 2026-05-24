<?php

namespace App\Http\Requests\Admin;

use App\Models\InfluencerSocialHandle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminInfluencerHandleStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    InfluencerSocialHandle::STATUS_PENDING,
                    InfluencerSocialHandle::STATUS_APPROVED,
                    InfluencerSocialHandle::STATUS_REJECTED,
                ]),
            ],
        ];
    }
}
