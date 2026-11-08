<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Role Anggota') }}</h2>
            @if (auth()->user()->role === 'admin')
                <a href="{{ route('roles.create') }}" class="rounded bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
                    + Tambah Role
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <x-flash />

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto p-6">
                    <table class="min-w-full border border-gray-200 bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-sm uppercase leading-normal text-gray-600">
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-left">Slug</th>
                                <th class="px-4 py-3 text-left">Deskripsi</th>
                                <th class="px-4 py-3 text-center">Jumlah Anggota</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-light text-gray-600">
                            @forelse ($roles as $role)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $role->name }}</td>
                                    <td class="px-4 py-3">{{ $role->slug }}</td>
                                    <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($role->description, 80) ?: '-' }}</td>
                                    <td class="px-4 py-3 text-center">{{ $role->members_count }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2 text-xs">
                                            @if (auth()->user()->role === 'admin')
                                                <a href="{{ route('roles.edit', $role) }}" class="font-semibold text-yellow-600 hover:text-yellow-800">Edit</a>
                                                <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                                      onsubmit="return confirm('Hapus role ini?');">
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
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada role.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">{{ $roles->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
