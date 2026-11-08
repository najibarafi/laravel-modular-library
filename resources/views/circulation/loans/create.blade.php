<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Buat Peminjaman') }}</h2>
            <a href="{{ route('loans.index') }}" class="rounded bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <x-flash />

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form action="{{ route('loans.store') }}" method="POST" id="loan-form">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="member_id" class="block text-sm font-medium text-gray-700">Anggota *</label>
                            <select name="member_id" id="member_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">- Pilih Anggota -</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member['id'] }}" @selected((int) old('member_id') === $member['id'])>
                                        {{ $member['name'] }} ({{ $member['membership_number'] }}) —
                                        {{ $member['active_loans'] }}/{{ $member['max_loans'] }} peminjaman
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-400">Hanya anggota aktif dan belum mencapai batas peminjaman yang ditampilkan.</p>
                            @error('member_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="block text-sm font-medium text-gray-700">Buku yang Dipinjam *</span>
                                <button type="button" id="add-item"
                                        class="rounded bg-gray-200 px-3 py-1 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                                    + Tambah Baris
                                </button>
                            </div>

                            <div id="items-container" class="space-y-3">
                                @php
                                    $oldItems = old('items');
                                    $rowCount = is_array($oldItems) && count($oldItems) > 0 ? count($oldItems) : 3;
                                @endphp

                                @for ($i = 0; $i < $rowCount; $i++)
                                    <div class="item-row grid grid-cols-1 gap-3 rounded border border-gray-200 p-3 md:grid-cols-12" data-row>
                                        <div class="md:col-span-8">
                                            <label class="block text-xs font-medium text-gray-500">Buku</label>
                                            <select name="items[{{ $i }}][book_id]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                                <option value="">- Pilih Buku -</option>
                                                @foreach ($books as $book)
                                                    <option value="{{ $book['id'] }}" @selected((int) old("items.{$i}.book_id") === $book['id'])>
                                                        {{ $book['title'] }} — stok {{ $book['stock'] }} ({{ $book['isbn'] }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-500">Jumlah</label>
                                            <input type="number" name="items[{{ $i }}][quantity]"
                                                   value="{{ old("items.{$i}.quantity", 1) }}" min="1" max="10"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        </div>
                                        <div class="flex items-end md:col-span-2">
                                            <button type="button" class="remove-row w-full rounded bg-red-100 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-200">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            @error('items') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            @error('items.*.book_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            @error('items.*.quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            @error('stock') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            @error('book') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes') }}</textarea>
                            @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2 rounded bg-gray-50 p-3 text-sm text-gray-600">
                            <p><strong>Transaksi atomik:</strong> stok dikurangi, record peminjaman dibuat, dan status anggota
                                diperbarui dalam satu transaksi. Jika stok tidak mencukupi, semua perubahan di-rollback.</p>
                            <p class="mt-1">Batas peminjaman: {{ \Modules\Circulation\Services\LoanService::LOAN_DAYS }} hari sejak tanggal pinjam.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button type="submit" class="rounded bg-blue-600 px-5 py-2 font-bold text-white hover:bg-blue-700">
                            Simpan Peminjaman
                        </button>
                        <a href="{{ route('loans.index') }}" class="rounded bg-gray-200 px-5 py-2 font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Template baris item baru --}}
    <template id="item-row-template">
        <div class="item-row grid grid-cols-1 gap-3 rounded border border-gray-200 p-3 md:grid-cols-12" data-row>
            <div class="md:col-span-8">
                <label class="block text-xs font-medium text-gray-500">Buku</label>
                <select name="items[__INDEX__][book_id]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">- Pilih Buku -</option>
                    @foreach ($books as $book)
                        <option value="{{ $book['id'] }}">
                            {{ $book['title'] }} — stok {{ $book['stock'] }} ({{ $book['isbn'] }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-500">Jumlah</label>
                <input type="number" name="items[__INDEX__][quantity]" value="1" min="1" max="10"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="flex items-end md:col-span-2">
                <button type="button" class="remove-row w-full rounded bg-red-100 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-200">
                    Hapus
                </button>
            </div>
        </div>
    </template>

    @push('scripts')
        <script>
            (function () {
                const container = document.getElementById('items-container');
                const template = document.getElementById('item-row-template');
                let nextIndex = {{ $rowCount }};

                document.getElementById('add-item').addEventListener('click', function () {
                    const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
                    container.insertAdjacentHTML('beforeend', html);
                });

                container.addEventListener('click', function (event) {
                    const removeButton = event.target.closest('.remove-row');
                    if (!removeButton) {
                        return;
                    }

                    const rows = container.querySelectorAll('[data-row]');
                    if (rows.length <= 1) {
                        return;
                    }

                    removeButton.closest('[data-row]').remove();
                });
            })();
        </script>
    @endpush
</x-app-layout>
