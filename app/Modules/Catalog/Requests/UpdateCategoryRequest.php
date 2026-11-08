<?php

namespace Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string', 'max:5000'],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
                Rule::notIn([$categoryId]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'slug.unique' => 'Slug kategori sudah digunakan.',
            'parent_id.exists' => 'Kategori induk tidak valid.',
            'parent_id.not_in' => 'Kategori tidak boleh menjadi induk dirinya sendiri.',
        ];
    }
}
