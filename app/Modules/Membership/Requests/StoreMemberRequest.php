<?php

namespace Modules\Membership\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'membership_number' => ['required', 'string', 'max:50', 'unique:members,membership_number'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:members,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:active,borrowing,suspended,inactive'],
            'max_loans' => ['required', 'integer', 'min:1', 'max:20'],
            'joined_at' => ['required', 'date'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],

            // Data keanggotaan awal (Membership Context).
            'membership_type' => ['required', 'in:basic,premium,student'],
            'membership_start_date' => ['required', 'date'],
            'membership_end_date' => ['nullable', 'date', 'after_or_equal:membership_start_date'],
            'membership_fee' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'membership_number.required' => 'Nomor keanggotaan wajib diisi.',
            'membership_number.unique' => 'Nomor keanggotaan sudah digunakan.',
            'name.required' => 'Nama anggota wajib diisi.',
            'email.required' => 'Email anggota wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email anggota sudah terdaftar.',
            'status.in' => 'Status anggota tidak valid.',
            'max_loans.min' => 'Batas peminjaman minimal 1.',
            'joined_at.required' => 'Tanggal bergabung wajib diisi.',
            'role_ids.*.exists' => 'Role yang dipilih tidak valid.',
            'membership_type.in' => 'Tipe keanggotaan tidak valid.',
            'membership_start_date.required' => 'Tanggal mulai keanggotaan wajib diisi.',
            'membership_end_date.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
            'membership_fee.required' => 'Biaya keanggotaan wajib diisi.',
            'membership_fee.min' => 'Biaya keanggotaan tidak boleh negatif.',
        ];
    }
}
