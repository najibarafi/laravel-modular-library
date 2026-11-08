<?php

namespace Modules\Circulation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Buang baris item yang kosong (misalnya baris form yang tidak dipakai)
     * sebelum validasi dijalankan.
     */
    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->filter(fn ($item) => is_array($item) && ! empty($item['book_id']))
            ->map(fn ($item) => [
                'book_id' => (int) $item['book_id'],
                'quantity' => (int) ($item['quantity'] ?? 1),
            ])
            ->values()
            ->all();

        $this->merge(['items' => $items]);
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.book_id' => ['required', 'integer', 'distinct', 'exists:books,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'Anggota wajib dipilih.',
            'member_id.exists' => 'Anggota tidak valid.',
            'items.required' => 'Minimal satu buku harus dipilih.',
            'items.min' => 'Minimal satu buku harus dipilih.',
            'items.*.book_id.required' => 'Buku wajib dipilih pada setiap baris.',
            'items.*.book_id.distinct' => 'Buku yang sama tidak boleh dipilih dua kali.',
            'items.*.book_id.exists' => 'Buku tidak valid.',
            'items.*.quantity.required' => 'Jumlah wajib diisi.',
            'items.*.quantity.min' => 'Jumlah minimal 1.',
            'items.*.quantity.max' => 'Jumlah maksimal 10 per buku.',
        ];
    }
}
