@php
    $editing = isset($member) && $member;
    $selected = array_map('intval', old('role_ids', $selectedRoles ?? []));
@endphp

<form action="{{ $editing ? route('members.update', $member) : route('members.store') }}" method="POST">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="membership_number" class="block text-sm font-medium text-gray-700">Nomor Keanggotaan *</label>
            <input type="text" name="membership_number" id="membership_number"
                   value="{{ old('membership_number', $member->membership_number ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @error('membership_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nama Anggota *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $member->name ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
            <input type="email" name="email" id="email" value="{{ old('email', $member->email ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Telepon</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $member->phone ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
            <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @foreach (['active' => 'Aktif', 'borrowing' => 'Sedang Meminjam', 'suspended' => 'Ditangguhkan', 'inactive' => 'Nonaktif'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $member->status ?? 'active') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="max_loans" class="block text-sm font-medium text-gray-700">Batas Peminjaman *</label>
            <input type="number" name="max_loans" id="max_loans" min="1" max="20"
                   value="{{ old('max_loans', $member->max_loans ?? 3) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @error('max_loans') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="joined_at" class="block text-sm font-medium text-gray-700">Tanggal Bergabung *</label>
            <input type="date" name="joined_at" id="joined_at"
                   value="{{ old('joined_at', isset($member) && $member?->joined_at ? $member->joined_at->format('Y-m-d') : now()->format('Y-m-d')) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @error('joined_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
            <textarea name="address" id="address" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('address', $member->address ?? '') }}</textarea>
            @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <span class="block text-sm font-medium text-gray-700">Role Anggota (Many-to-Many, pivot assigned_at)</span>
            <div class="mt-2 grid grid-cols-1 gap-2 rounded-md border border-gray-200 p-3 md:grid-cols-3">
                @foreach ($roles as $role)
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="role_ids[]" value="{{ $role->id }}"
                               @checked(in_array($role->id, $selected, true))
                               class="rounded border-gray-300">
                        {{ $role->name }}
                    </label>
                @endforeach
            </div>
            @error('role_ids') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('role_ids.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        @unless ($editing)
            <div class="md:col-span-2 mt-2 border-t border-gray-200 pt-4">
                <h3 class="mb-2 text-md font-semibold text-gray-800">Keanggotaan Awal</h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="membership_type" class="block text-sm font-medium text-gray-700">Tipe Keanggotaan *</label>
                        <select name="membership_type" id="membership_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @foreach (['basic' => 'Basic', 'premium' => 'Premium', 'student' => 'Student'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('membership_type', 'basic') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('membership_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="membership_fee" class="block text-sm font-medium text-gray-700">Biaya Keanggotaan *</label>
                        <input type="number" name="membership_fee" id="membership_fee" min="0" step="0.01"
                               value="{{ old('membership_fee', 0) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        @error('membership_fee') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="membership_start_date" class="block text-sm font-medium text-gray-700">Mulai *</label>
                        <input type="date" name="membership_start_date" id="membership_start_date"
                               value="{{ old('membership_start_date', now()->format('Y-m-d')) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        @error('membership_start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="membership_end_date" class="block text-sm font-medium text-gray-700">Berakhir (opsional)</label>
                        <input type="date" name="membership_end_date" id="membership_end_date"
                               value="{{ old('membership_end_date', now()->addYear()->format('Y-m-d')) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('membership_end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        @endunless
    </div>

    <div class="mt-6 flex items-center gap-3">
        <button type="submit" class="rounded bg-blue-600 px-5 py-2 font-bold text-white hover:bg-blue-700">
            {{ $editing ? 'Perbarui Anggota' : 'Simpan Anggota' }}
        </button>
        <a href="{{ route('members.index') }}" class="rounded bg-gray-200 px-5 py-2 font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
    </div>
</form>
