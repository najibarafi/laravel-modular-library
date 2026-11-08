<?php

namespace Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Catalog\Rules\IsbnRule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bookId = $this->route('book')?->id;

        return [
            'author_id' => ['nullable', 'integer', 'exists:authors,id'],
            'publisher_id' => ['nullable', 'integer', 'exists:publishers,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('books', 'slug')->ignore($bookId)],
            'isbn' => ['sometimes', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($bookId), new IsbnRule()],
            'description' => ['nullable', 'string', 'max:5000'],
            'published_year' => ['nullable', 'integer', 'min:1000', 'max:' . date('Y')],
            'language' => ['nullable', 'string', 'max:50'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Judul buku maksimal 255 karakter.',
            'isbn.unique' => 'ISBN sudah terdaftar pada buku lain.',
            'author_id.exists' => 'Penulis yang dipilih tidak valid.',
            'publisher_id.exists' => 'Penerbit yang dipilih tidak valid.',
            'published_year.max' => 'Tahun terbit tidak boleh melebihi tahun sekarang.',
            'stock.integer' => 'Stok harus berupa bilangan bulat.',
            'stock.min' => 'Stok tidak boleh negatif.',
            'category_ids.*.exists' => 'Kategori yang dipilih tidak valid.',
        ];
    }
}
