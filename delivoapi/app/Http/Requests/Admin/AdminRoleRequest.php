<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminRoleRequest extends FormRequest
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
            'name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9_.-]+$/',
                'unique:roles,name'.($id ? ",{$id}" : ''),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Role name can only contain lowercase letters, numbers, dots, underscores and hyphens.',
        ];
    }
}
