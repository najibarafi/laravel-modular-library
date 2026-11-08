<?php

namespace Modules\Membership\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:basic,premium,student'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'fee' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,expired,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Tipe keanggotaan tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
            'fee.required' => 'Biaya wajib diisi.',
            'fee.min' => 'Biaya tidak boleh negatif.',
        ];
    }
}
