<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Kategori Buku') }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('categories.index', ['trashed' => request()->boolean('trashed') ? 0 : 1]) }}"
                   class="rounded bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                    {{ request()->boolean('trashed') ? 'Lihat Kategori Aktif' : 'Lihat Kategori Terhapus' }}
                </a>
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('categories.create') }}"
                       class="rounded bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
                        + Tambah Kategori
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-flash />

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto p-6">
                    <table class="min-w-full border border-gray-200 bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-sm uppercase leading-normal text-gray-600">
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-left">Slug</th>
                                <th class="px-4 py-3 text-left">Induk</th>
                                <th class="px-4 py-3 text-left">Sub-kategori</th>
                                <th class="px-4 py-3 text-center">Jumlah Buku</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-light text-gray-600">
                            @forelse ($categories as $category)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $category->name }}</td>
                                    <td class="px-4 py-3">{{ $category->slug }}</td>
                                    <td class="px-4 py-3">{{ $category->parent?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @forelse ($category->children as $child)
                                            <span class="mr-1 rounded bg-gray-100 px-2 py-0.5 text-xs">{{ $child->name }}</span>
                                        @empty
                                            <span class="text-xs text-gray-400">-</span>
                                        @endforelse
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ $category->books_count }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($category->trashed())
                                            <span class="rounded bg-gray-200 px-2 py-0.5 text-xs text-gray-700">Terhapus</span>
                                        @else
                                            <span class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">Aktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap items-center justify-center gap-2 text-xs">
                                            @if (auth()->user()->role === 'admin')
                                                @if ($category->trashed())
                                                    <form action="{{ route('categories.restore', $category->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="font-semibold text-green-600 hover:text-green-800">Restore</button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('categories.edit', $category) }}" class="font-semibold text-yellow-600 hover:text-yellow-800">Edit</a>
                                                    <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                                          onsubmit="return confirm('Hapus kategori ini? Data akan di-soft delete.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="font-semibold text-red-600 hover:text-red-800">Hapus</button>
                                                    </form>
                                                @endif
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada kategori.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">{{ $categories->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
