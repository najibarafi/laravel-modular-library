<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $member->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('members.index') }}" class="rounded bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Kembali</a>
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('members.edit', $member) }}" class="rounded bg-yellow-500 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-600">Edit</a>
                    <form action="{{ route('members.destroy', $member) }}" method="POST"
                          onsubmit="return confirm('Hapus anggota ini? Data akan di-soft delete.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Hapus</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
            <x-flash />

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Informasi Anggota</h3>

                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Nomor Keanggotaan</dt>
                            <dd class="font-medium text-gray-800">{{ $member->membership_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Status</dt>
                            <dd class="font-medium text-gray-800">{{ $member->status }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Email</dt>
                            <dd class="font-medium text-gray-800">{{ $member->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Telepon</dt>
                            <dd class="font-medium text-gray-800">{{ $member->phone ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Peminjaman Aktif</dt>
                            <dd class="font-medium text-gray-800">{{ $member->active_loans }} / {{ $member->max_loans }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Tanggal Bergabung</dt>
                            <dd class="font-medium text-gray-800">{{ $member->joined_at?->format('d M Y') }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs uppercase text-gray-400">Alamat</dt>
                            <dd class="mt-1 whitespace-pre-line text-gray-700">{{ $member->address ?: '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Role</h3>

                    @if (auth()->user()->role === 'admin')
                        <form action="{{ route('members.roles.update', $member) }}" method="POST">
                            @csrf
                            <div class="space-y-2">
                                @foreach ($roles as $role)
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="role_ids[]" value="{{ $role->id }}"
                                               @checked(in_array($role->id, $selectedRoles, true))
                                               class="rounded border-gray-300">
                                        {{ $role->name }}
                                    </label>
                                @endforeach
                            </div>
                            <button type="submit" class="mt-4 rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Simpan Role
                            </button>
                        </form>
                    @else
                        <div class="flex flex-wrap gap-1">
                            @forelse ($member->roles as $role)
                                <span class="rounded bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">{{ $role->name }}</span>
                            @empty
                                <span class="text-sm text-gray-400">Belum ada role.</span>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Riwayat Keanggotaan</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-sm uppercase leading-normal text-gray-600">
                                <th class="px-4 py-3 text-left">Tipe</th>
                                <th class="px-4 py-3 text-left">Mulai</th>
                                <th class="px-4 py-3 text-left">Berakhir</th>
                                <th class="px-4 py-3 text-right">Biaya</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-light text-gray-600">
                            @forelse ($member->memberships as $membership)
                                <tr class="border-b border-gray-200">
                                    <td class="px-4 py-3">{{ ucfirst($membership->type) }}</td>
                                    <td class="px-4 py-3">{{ $membership->start_date?->format('d M Y') }}</td>
                                    <td class="px-4 py-3">{{ $membership->end_date?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right">Rp {{ number_format((float) $membership->fee, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="rounded px-2 py-0.5 text-xs {{ $membership->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' }}">
                                            {{ $membership->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if (auth()->user()->role === 'admin')
                                            <form action="{{ route('memberships.destroy', $membership) }}" method="POST"
                                                  onsubmit="return confirm('Hapus data keanggotaan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800">Hapus</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada data keanggotaan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (auth()->user()->role === 'admin')
                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <h4 class="mb-3 font-semibold text-gray-800">Tambah Keanggotaan</h4>

                        <form action="{{ route('members.memberships.store', $member) }}" method="POST"
                              class="grid grid-cols-1 items-end gap-3 md:grid-cols-5">
                            @csrf

                            <div>
                                <label class="block text-xs font-medium text-gray-500">Tipe</label>
                                <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="basic">Basic</option>
                                    <option value="premium">Premium</option>
                                    <option value="student">Student</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500">Mulai</label>
                                <input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500">Berakhir</label>
                                <input type="date" name="end_date" value="{{ now()->addYear()->format('Y-m-d') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500">Biaya</label>
                                <input type="number" name="fee" min="0" step="0.01" value="0"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500">Status</label>
                                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>

                            <div class="md:col-span-5">
                                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                    Tambah Keanggotaan
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
