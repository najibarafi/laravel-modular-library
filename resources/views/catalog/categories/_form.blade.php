@php($editing = isset($category) && $category)

<form action="{{ $editing ? route('categories.update', $category) : route('categories.store') }}" method="POST">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nama Kategori *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $category->name ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug ?? '') }}"
                   placeholder="Kosongkan untuk otomatis dari nama"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="parent_id" class="block text-sm font-medium text-gray-700">Kategori Induk (opsional)</label>
            <select name="parent_id" id="parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="">- Jadikan kategori utama -</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" @selected((int) old('parent_id', $category->parent_id ?? 0) === $parent->id)>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-400">Relasi self-referencing: kategori dapat memiliki sub-kategori.</p>
            @error('parent_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
            <textarea name="description" id="description" rows="4"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $category->description ?? '') }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="mt-6 flex items-center gap-3">
        <button type="submit" class="rounded bg-blue-600 px-5 py-2 font-bold text-white hover:bg-blue-700">
            {{ $editing ? 'Perbarui Kategori' : 'Simpan Kategori' }}
        </button>
        <a href="{{ route('categories.index') }}" class="rounded bg-gray-200 px-5 py-2 font-semibold text-gray-700 hover:bg-gray-300">
            Batal
        </a>
    </div>
</form>
