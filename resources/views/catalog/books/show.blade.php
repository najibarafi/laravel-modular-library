<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $book->title }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('books.index') }}" class="rounded bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                    Kembali
                </a>
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('books.edit', $book) }}" class="rounded bg-yellow-500 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-600">
                        Edit
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <x-flash />

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Informasi Buku</h3>

                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase text-gray-400">ISBN</dt>
                            <dd class="font-medium text-gray-800">{{ $book->isbn_formatted }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Tahun Terbit</dt>
                            <dd class="font-medium text-gray-800">{{ $book->published_year ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Penulis</dt>
                            <dd class="font-medium text-gray-800">{{ $book->author?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Penerbit</dt>
                            <dd class="font-medium text-gray-800">{{ $book->publisher?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Bahasa</dt>
                            <dd class="font-medium text-gray-800">{{ $book->language }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Stok</dt>
                            <dd class="font-medium {{ $book->stock <= $book->low_stock_threshold ? 'text-red-600' : 'text-gray-800' }}">
                                {{ $book->stock }} (batas menipis: {{ $book->low_stock_threshold }})
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Status</dt>
                            <dd class="font-medium text-gray-800">
                                {{ $book->trashed() ? 'Terhapus (soft delete)' : ($book->is_active ? 'Aktif' : 'Nonaktif') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Dibuat</dt>
                            <dd class="font-medium text-gray-800">{{ $book->created_at?->format('d M Y H:i') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        <dt class="text-xs uppercase text-gray-400">Deskripsi</dt>
                        <dd class="mt-1 whitespace-pre-line text-gray-700">{{ $book->description ?: '-' }}</dd>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Kategori</h3>

                    @forelse ($book->categories as $category)
                        <div class="mb-2 rounded border border-gray-200 p-2">
                            <div class="font-medium text-gray-800">{{ $category->name }}</div>
                            <div class="text-xs text-gray-500">Induk: {{ $category->parent?->name ?? '-' }}</div>
                            <div class="text-xs text-gray-400">
                                added_at: {{ $category->pivot->added_at }}
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada kategori.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
