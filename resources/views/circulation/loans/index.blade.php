<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Peminjaman Buku') }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('loans.index', array_merge(request()->query(), ['trashed' => request()->boolean('trashed') ? 0 : 1])) }}"
                   class="rounded bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                    {{ request()->boolean('trashed') ? 'Lihat Peminjaman Aktif' : 'Lihat Peminjaman Terhapus' }}
                </a>
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('loans.create') }}"
                       class="rounded bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
                        + Buat Peminjaman
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-flash />

            <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('loans.index') }}" class="flex flex-wrap items-end gap-3">
                    <input type="hidden" name="trashed" value="{{ request()->boolean('trashed') ? 1 : 0 }}">

                    <div class="min-w-64 flex-1">
                        <label class="block text-xs font-medium text-gray-500">Cari (no. peminjaman / nama anggota)</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Semua</option>
                            <option value="borrowed" @selected(request('status') === 'borrowed')>Dipinjam</option>
                            <option value="returned" @selected(request('status') === 'returned')>Dikembalikan</option>
                        </select>
                    </div>

                    <label class="inline-flex items-center gap-2 pb-2 text-sm text-gray-600">
                        <input type="checkbox" name="overdue" value="1" @checked(request()->boolean('overdue'))
                               class="rounded border-gray-300">
                        Hanya terlambat
                    </label>

                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
                    <a href="{{ route('loans.index') }}" class="rounded bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Reset</a>
                </form>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto p-6">
                    <table class="min-w-full border border-gray-200 bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-sm uppercase leading-normal text-gray-600">
                                <th class="px-4 py-3 text-left">No. Peminjaman</th>
                                <th class="px-4 py-3 text-left">Anggota</th>
                                <th class="px-4 py-3 text-left">Tanggal Pinjam</th>
                                <th class="px-4 py-3 text-left">Jatuh Tempo</th>
                                <th class="px-4 py-3 text-center">Item</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Denda</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-light text-gray-600">
                            @forelse ($loans as $loan)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('loans.show', $loan) }}" class="font-semibold text-blue-700 hover:underline">
                                            {{ $loan->loan_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $loan->member_name }}
                                        <div class="text-xs text-gray-400">{{ $loan->member?->membership_number }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ $loan->borrowed_at?->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-3">{{ $loan->due_at?->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-3 text-center">{{ $loan->items->sum('quantity') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($loan->trashed())
                                            <span class="rounded bg-gray-200 px-2 py-0.5 text-xs text-gray-700">Terhapus</span>
                                        @elseif ($loan->isReturned())
                                            <span class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">Dikembalikan</span>
                                        @elseif ($loan->isOverdue())
                                            <span class="rounded bg-red-100 px-2 py-0.5 text-xs text-red-700">Terlambat</span>
                                        @else
                                            <span class="rounded bg-blue-100 px-2 py-0.5 text-xs text-blue-700">Dipinjam</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        Rp {{ number_format((float) $loan->total_fine, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap items-center justify-center gap-2 text-xs">
                                            <a href="{{ route('loans.show', $loan) }}" class="font-semibold text-blue-600 hover:text-blue-800">Detail</a>

                                            @if (auth()->user()->role === 'admin')
                                                @if ($loan->trashed())
                                                    <form action="{{ route('loans.restore', $loan->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="font-semibold text-green-600 hover:text-green-800">Restore</button>
                                                    </form>
                                                @else
                                                    @if (! $loan->isReturned())
                                                        <form action="{{ route('loans.return', $loan) }}" method="POST"
                                                              onsubmit="return confirm('Kembalikan semua buku pada peminjaman ini?');">
                                                            @csrf
                                                            <input type="hidden" name="notes" value="Dikembalikan dari daftar peminjaman.">
                                                            <button type="submit" class="font-semibold text-indigo-600 hover:text-indigo-800">Kembalikan</button>
                                                        </form>
                                                    @endif
                                                    <form action="{{ route('loans.destroy', $loan) }}" method="POST"
                                                          onsubmit="return confirm('Hapus peminjaman ini? Data akan di-soft delete.');">
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
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">Belum ada data peminjaman.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">{{ $loans->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
