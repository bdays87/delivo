<?php

namespace App\Http\Requests\Vendor;

use App\Models\VendorKycDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorKycUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in([VendorKycDocument::TYPE_NATIONAL_ID])],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // 5 MB
        ];
    }
}
