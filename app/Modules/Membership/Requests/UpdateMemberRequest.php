<?php

namespace Modules\Membership\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $memberId = $this->route('member')?->id;

        return [
            'membership_number' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('members', 'membership_number')->ignore($memberId),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('members', 'email')->ignore($memberId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'in:active,borrowing,suspended,inactive'],
            'max_loans' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'joined_at' => ['sometimes', 'date'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'membership_number.unique' => 'Nomor keanggotaan sudah digunakan.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email anggota sudah terdaftar.',
            'status.in' => 'Status anggota tidak valid.',
            'max_loans.min' => 'Batas peminjaman minimal 1.',
            'role_ids.*.exists' => 'Role yang dipilih tidak valid.',
        ];
    }
}
