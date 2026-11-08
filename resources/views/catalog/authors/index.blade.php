<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Penulis') }}</h2>
            @if (auth()->user()->role === 'admin')
                <a href="{{ route('authors.create') }}" class="rounded bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
                    + Tambah Penulis
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <x-flash />

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto p-6">
                    <table class="min-w-full border border-gray-200 bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-sm uppercase leading-normal text-gray-600">
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-left">Slug</th>
                                <th class="px-4 py-3 text-left">Biografi</th>
                                <th class="px-4 py-3 text-center">Jumlah Buku</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-light text-gray-600">
                            @forelse ($authors as $author)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $author->name }}</td>
                                    <td class="px-4 py-3">{{ $author->slug }}</td>
                                    <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($author->bio, 80) ?: '-' }}</td>
                                    <td class="px-4 py-3 text-center">{{ $author->books_count }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2 text-xs">
                                            @if (auth()->user()->role === 'admin')
                                                <a href="{{ route('authors.edit', $author) }}" class="font-semibold text-yellow-600 hover:text-yellow-800">Edit</a>
                                                <form action="{{ route('authors.destroy', $author) }}" method="POST"
                                                      onsubmit="return confirm('Hapus penulis ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-semibold text-red-600 hover:text-red-800">Hapus</button>
                                                </form>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada penulis.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">{{ $authors->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
