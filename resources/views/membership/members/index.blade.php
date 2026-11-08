<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Anggota Perpustakaan') }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('members.index', array_merge(request()->query(), ['trashed' => request()->boolean('trashed') ? 0 : 1])) }}"
                   class="rounded bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                    {{ request()->boolean('trashed') ? 'Lihat Anggota Aktif' : 'Lihat Anggota Terhapus' }}
                </a>
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('members.create') }}"
                       class="rounded bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
                        + Tambah Anggota
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-flash />

            <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('members.index') }}" class="flex flex-wrap items-end gap-3">
                    <input type="hidden" name="trashed" value="{{ request()->boolean('trashed') ? 1 : 0 }}">

                    <div class="min-w-64 flex-1">
                        <label class="block text-xs font-medium text-gray-500">Cari (nama / nomor / email)</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Semua</option>
                            @foreach (['active' => 'Aktif', 'borrowing' => 'Meminjam', 'suspended' => 'Ditangguhkan', 'inactive' => 'Nonaktif'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
                    <a href="{{ route('members.index') }}" class="rounded bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Reset</a>
                </form>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto p-6">
                    <table class="min-w-full border border-gray-200 bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-sm uppercase leading-normal text-gray-600">
                                <th class="px-4 py-3 text-left">No. Anggota</th>
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Peminjaman</th>
                                <th class="px-4 py-3 text-left">Role</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-light text-gray-600">
                            @forelse ($members as $member)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $member->membership_number }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('members.show', $member) }}" class="font-semibold text-blue-700 hover:underline">
                                            {{ $member->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">{{ $member->email }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $statusClass = match ($member->status) {
                                                'active' => 'bg-green-100 text-green-700',
                                                'borrowing' => 'bg-blue-100 text-blue-700',
                                                'suspended' => 'bg-red-100 text-red-700',
                                                default => 'bg-gray-200 text-gray-700',
                                            };
                                        @endphp
                                        <span class="rounded px-2 py-0.5 text-xs {{ $statusClass }}">
                                            {{ $member->trashed() ? 'Terhapus' : $member->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ $member->active_loans }} / {{ $member->max_loans }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($member->roles as $role)
                                                <span class="rounded bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-xs text-gray-400">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap items-center justify-center gap-2 text-xs">
                                            <a href="{{ route('members.show', $member) }}" class="font-semibold text-blue-600 hover:text-blue-800">Detail</a>

                                            @if (auth()->user()->role === 'admin')
                                                @if ($member->trashed())
                                                    <form action="{{ route('members.restore', $member->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="font-semibold text-green-600 hover:text-green-800">Restore</button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('members.edit', $member) }}" class="font-semibold text-yellow-600 hover:text-yellow-800">Edit</a>
                                                    <form action="{{ route('members.destroy', $member) }}" method="POST"
                                                          onsubmit="return confirm('Hapus anggota ini? Data akan di-soft delete.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="font-semibold text-red-600 hover:text-red-800">Hapus</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada anggota.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">{{ $members->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
