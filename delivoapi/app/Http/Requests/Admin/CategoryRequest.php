<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
            'parent_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
                Rule::notIn(array_filter([(int) $id])),
            ],
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:120'],
            'slug' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:categories,slug'.($id ? ",{$id}" : ''),
            ],
            'icon' => ['sometimes', 'string', 'max:80'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'tint' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000'],
            'status' => [
                'sometimes',
                Rule::in([Category::STATUS_ACTIVE, Category::STATUS_ARCHIVED]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug may only contain lowercase letters, numbers and hyphens.',
            'parent_id.not_in' => 'A category cannot be its own parent.',
        ];
    }
}
