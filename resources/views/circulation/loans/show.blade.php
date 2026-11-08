<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Peminjaman {{ $loan->loan_number }}
                </h2>
                <p class="text-sm text-gray-500">Dibuat {{ $loan->created_at?->format('d M Y H:i') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('loans.index') }}" class="rounded bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Kembali</a>
                @if (auth()->user()->role === 'admin' && ! $loan->isReturned())
                    <form action="{{ route('loans.return', $loan) }}" method="POST"
                          onsubmit="return confirm('Kembalikan semua buku pada peminjaman ini?');">
                        @csrf
                        <input type="hidden" name="notes" value="Dikembalikan dari halaman detail.">
                        <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Kembalikan Buku
                        </button>
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
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Informasi Peminjaman</h3>

                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Anggota (snapshot)</dt>
                            <dd class="font-medium text-gray-800">{{ $loan->member_name }}</dd>
                            <dd class="text-xs text-gray-500">
                                {{ $loan->member?->membership_number ?? 'Data anggota tidak tersedia' }}
                                @if ($loan->member)
                                    · {{ $loan->member->email }}
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Status</dt>
                            <dd class="font-medium text-gray-800">
                                @if ($loan->isReturned())
                                    <span class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">Dikembalikan</span>
                                @elseif ($loan->isOverdue())
                                    <span class="rounded bg-red-100 px-2 py-0.5 text-xs text-red-700">Terlambat</span>
                                @else
                                    <span class="rounded bg-blue-100 px-2 py-0.5 text-xs text-blue-700">Dipinjam</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Tanggal Pinjam</dt>
                            <dd class="font-medium text-gray-800">{{ $loan->borrowed_at?->format('d M Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Jatuh Tempo</dt>
                            <dd class="font-medium text-gray-800">{{ $loan->due_at?->format('d M Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Tanggal Kembali</dt>
                            <dd class="font-medium text-gray-800">{{ $loan->returned_at?->format('d M Y H:i') ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Total Denda</dt>
                            <dd class="font-medium {{ (float) $loan->total_fine > 0 ? 'text-red-600' : 'text-gray-800' }}">
                                Rp {{ number_format((float) $loan->total_fine, 0, ',', '.') }}
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs uppercase text-gray-400">Catatan</dt>
                            <dd class="mt-1 whitespace-pre-line text-gray-700">{{ $loan->notes ?: '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Ringkasan</h3>
                    <div class="space-y-2 text-sm text-gray-700">
                        <p>Jumlah judul: <strong>{{ $loan->items->count() }}</strong></p>
                        <p>Jumlah eksemplar: <strong>{{ $loan->items->sum('quantity') }}</strong></p>
                        <p>Denda belum dibayar:
                            <strong class="text-red-600">
                                Rp {{ number_format((float) $loan->fines->where('status', 'unpaid')->sum('amount'), 0, ',', '.') }}
                            </strong>
                        </p>
                    </div>

                    @if (auth()->user()->role === 'admin' && ! $loan->isReturned())
                        <form action="{{ route('loans.return', $loan) }}" method="POST" class="mt-4 border-t border-gray-200 pt-4">
                            @csrf
                            <label for="notes" class="block text-xs font-medium text-gray-500">Catatan pengembalian</label>
                            <textarea name="notes" id="notes" rows="2"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes') }}</textarea>
                            <button type="submit" class="mt-2 w-full rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                Proses Pengembalian
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Detail Peminjaman (snapshot)</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-sm uppercase leading-normal text-gray-600">
                                <th class="px-4 py-3 text-left">Judul Buku</th>
                                <th class="px-4 py-3 text-left">ISBN</th>
                                <th class="px-4 py-3 text-center">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-light text-gray-600">
                            @forelse ($loan->items as $item)
                                <tr class="border-b border-gray-200">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $item->book_title }}</td>
                                    <td class="px-4 py-3">{{ $item->book_isbn }}</td>
                                    <td class="px-4 py-3 text-center">{{ $item->quantity }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-500">Tidak ada item.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Denda</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200 bg-white">
                            <thead>
                                <tr class="bg-gray-100 text-sm uppercase leading-normal text-gray-600">
                                    <th class="px-3 py-2 text-center">Hari</th>
                                    <th class="px-3 py-2 text-right">Jumlah</th>
                                    <th class="px-3 py-2 text-center">Status</th>
                                    <th class="px-3 py-2 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm font-light text-gray-600">
                                @forelse ($loan->fines as $fine)
                                    <tr class="border-b border-gray-200">
                                        <td class="px-3 py-2 text-center">{{ $fine->days_late }}</td>
                                        <td class="px-3 py-2 text-right">Rp {{ number_format((float) $fine->amount, 0, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="rounded px-2 py-0.5 text-xs {{ $fine->isPaid() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $fine->status }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            @if (auth()->user()->role === 'admin' && ! $fine->isPaid())
                                                <form action="{{ route('fines.pay', $fine) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-xs font-semibold text-green-600 hover:text-green-800">Bayar</button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-gray-500">Tidak ada denda.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Riwayat Status</h3>

                    <div class="space-y-3">
                        @forelse ($loan->statusHistories as $history)
                            <div class="rounded border border-gray-200 p-3">
                                <div class="flex items-center justify-between">
                                    <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">{{ $history->status }}</span>
                                    <span class="text-xs text-gray-400">{{ $history->created_at?->format('d M Y H:i') }}</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-700">{{ $history->notes ?: '-' }}</p>
                                <p class="text-xs text-gray-400">oleh: {{ $history->changedBy?->name ?? 'system' }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada riwayat status.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
