<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Denda Peminjaman') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
            <x-flash />

            <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Total Denda Belum Dibayar</p>
                    <p class="text-2xl font-bold text-red-600">
                        Rp {{ number_format((float) $totalUnpaid, 0, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-lg bg-white p-4 shadow-sm md:col-span-2">
                    <form method="GET" action="{{ route('fines.index') }}" class="flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Status</label>
                            <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Semua</option>
                                <option value="unpaid" @selected(request('status') === 'unpaid')>Belum Dibayar</option>
                                <option value="paid" @selected(request('status') === 'paid')>Lunas</option>
                            </select>
                        </div>
                        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
                        <a href="{{ route('fines.index') }}" class="rounded bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Reset</a>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto p-6">
                    <table class="min-w-full border border-gray-200 bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-sm uppercase leading-normal text-gray-600">
                                <th class="px-4 py-3 text-left">No. Peminjaman</th>
                                <th class="px-4 py-3 text-left">Anggota</th>
                                <th class="px-4 py-3 text-center">Hari Terlambat</th>
                                <th class="px-4 py-3 text-right">Jumlah Denda</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-left">Dibayar Pada</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-light text-gray-600">
                            @forelse ($fines as $fine)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('loans.show', $fine->loan) }}" class="font-semibold text-blue-700 hover:underline">
                                            {{ $fine->loan?->loan_number ?? '-' }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">{{ $fine->loan?->member_name ?? $fine->member?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">{{ $fine->days_late }}</td>
                                    <td class="px-4 py-3 text-right">Rp {{ number_format((float) $fine->amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="rounded px-2 py-0.5 text-xs {{ $fine->isPaid() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $fine->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $fine->paid_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if (auth()->user()->role === 'admin' && ! $fine->isPaid())
                                            <form action="{{ route('fines.pay', $fine) }}" method="POST"
                                                  onsubmit="return confirm('Tandai denda ini sebagai lunas?');">
                                                @csrf
                                                <button type="submit" class="rounded bg-green-600 px-3 py-1 text-xs font-semibold text-white hover:bg-green-700">
                                                    Bayar
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada denda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">{{ $fines->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
