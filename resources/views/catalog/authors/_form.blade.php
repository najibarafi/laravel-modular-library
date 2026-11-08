@php($editing = isset($author) && $author)

<form action="{{ $editing ? route('authors.update', $author) : route('authors.store') }}" method="POST">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nama Penulis *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $author->name ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $author->slug ?? '') }}"
                   placeholder="Kosongkan untuk otomatis dari nama"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="bio" class="block text-sm font-medium text-gray-700">Biografi</label>
            <textarea name="bio" id="bio" rows="4"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('bio', $author->bio ?? '') }}</textarea>
            @error('bio') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="mt-6 flex items-center gap-3">
        <button type="submit" class="rounded bg-blue-600 px-5 py-2 font-bold text-white hover:bg-blue-700">
            {{ $editing ? 'Perbarui Penulis' : 'Simpan Penulis' }}
        </button>
        <a href="{{ route('authors.index') }}" class="rounded bg-gray-200 px-5 py-2 font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
    </div>
</form>
