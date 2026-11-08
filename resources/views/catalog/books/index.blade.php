<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Katalog Buku') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('books.index', array_merge(request()->query(), ['trashed' => request()->boolean('trashed') ? 0 : 1])) }}"
                   class="rounded bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                    {{ request()->boolean('trashed') ? 'Lihat Buku Aktif' : 'Lihat Buku Terhapus' }}
                </a>
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('books.create') }}"
                       class="rounded bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
                        + Tambah Buku
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-flash />

            {{-- Filter & pencarian --}}
            <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('books.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-6">
                    <input type="hidden" name="trashed" value="{{ request()->boolean('trashed') ? 1 : 0 }}">

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500">Cari (judul / ISBN / deskripsi)</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500">Kategori</label>
                        <select name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Semua</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) request('category_id') === $category->id)>
                                    {{ $category->name }}
                                </option>
                                @foreach ($category->children as $child)
                                    <option value="{{ $child->id }}" @selected((int) request('category_id') === $child->id)>
                                        &nbsp;&nbsp;— {{ $child->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500">Penulis</label>
                        <select name="author_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Semua</option>
                            @foreach ($authors as $author)
                                <option value="{{ $author->id }}" @selected((int) request('author_id') === $author->id)>
                                    {{ $author->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500">Penerbit</label>
                        <select name="publisher_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Semua</option>
                            @foreach ($publishers as $publisher)
                                <option value="{{ $publisher->id }}" @selected((int) request('publisher_id') === $publisher->id)>
                                    {{ $publisher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col justify-end gap-2">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="available" value="1" @checked(request()->boolean('available'))
                                   class="rounded border-gray-300">
                            Hanya tersedia
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="low_stock" value="1" @checked(request()->boolean('low_stock'))
                                   class="rounded border-gray-300">
                            Stok menipis
                        </label>
                    </div>

                    <div class="md:col-span-6 flex gap-2">
                        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Terapkan Filter
                        </button>
                        <a href="{{ route('books.index') }}" class="rounded bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto p-6">
                    <table class="min-w-full border border-gray-200 bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-sm uppercase leading-normal text-gray-600">
                                <th class="px-4 py-3 text-left">Judul</th>
                                <th class="px-4 py-3 text-left">ISBN</th>
                                <th class="px-4 py-3 text-left">Penulis</th>
                                <th class="px-4 py-3 text-left">Penerbit</th>
                                <th class="px-4 py-3 text-left">Kategori</th>
                                <th class="px-4 py-3 text-center">Stok</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-light text-gray-600">
                            @forelse ($books as $book)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('books.show', $book) }}" class="font-semibold text-blue-700 hover:underline">
                                            {{ $book->title }}
                                        </a>
                                        <div class="text-xs text-gray-400">{{ $book->slug }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ $book->isbn_formatted }}</td>
                                    <td class="px-4 py-3">{{ $book->author?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $book->publisher?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($book->categories as $category)
                                                <span class="rounded bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">{{ $category->name }}</span>
                                            @empty
                                                <span class="text-xs text-gray-400">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="{{ $book->stock <= $book->low_stock_threshold ? 'font-bold text-red-600' : '' }}">
                                            {{ $book->stock }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($book->trashed())
                                            <span class="rounded bg-gray-200 px-2 py-0.5 text-xs text-gray-700">Terhapus</span>
                                        @elseif ($book->is_active)
                                            <span class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">Aktif</span>
                                        @else
                                            <span class="rounded bg-yellow-100 px-2 py-0.5 text-xs text-yellow-700">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap items-center justify-center gap-2 text-xs">
                                            <a href="{{ route('books.show', $book) }}" class="font-semibold text-blue-600 hover:text-blue-800">Detail</a>

                                            @if (auth()->user()->role === 'admin')
                                                @if ($book->trashed())
                                                    <form action="{{ route('books.restore', $book->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="font-semibold text-green-600 hover:text-green-800">Restore</button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('books.edit', $book) }}" class="font-semibold text-yellow-600 hover:text-yellow-800">Edit</a>
                                                    <form action="{{ route('books.destroy', $book) }}" method="POST"
                                                          onsubmit="return confirm('Hapus buku ini? Data akan di-soft delete.');">
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
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                        Tidak ada data buku yang cocok dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $books->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
