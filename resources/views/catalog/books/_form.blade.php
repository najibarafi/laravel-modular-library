@php
    $editing = isset($book) && $book;
    $selected = array_map('intval', old('category_ids', $selectedCategories ?? []));
@endphp

<form action="{{ $editing ? route('books.update', $book) : route('books.store') }}" method="POST">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="title" class="block text-sm font-medium text-gray-700">Judul Buku *</label>
            <input type="text" name="title" id="title" value="{{ old('title', $book->title ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $book->slug ?? '') }}"
                   placeholder="Kosongkan untuk otomatis dari judul"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="isbn" class="block text-sm font-medium text-gray-700">ISBN *</label>
            <input type="text" name="isbn" id="isbn" value="{{ old('isbn', $book->isbn ?? '') }}"
                   placeholder="Contoh: 9780306406157"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            <p class="mt-1 text-xs text-gray-400">ISBN-10 atau ISBN-13, checksum divalidasi Value Object.</p>
            @error('isbn') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="author_id" class="block text-sm font-medium text-gray-700">Penulis</label>
            <select name="author_id" id="author_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="">- Pilih Penulis -</option>
                @foreach ($authors as $author)
                    <option value="{{ $author->id }}" @selected((int) old('author_id', $book->author_id ?? 0) === $author->id)>
                        {{ $author->name }}
                    </option>
                @endforeach
            </select>
            @error('author_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="publisher_id" class="block text-sm font-medium text-gray-700">Penerbit</label>
            <select name="publisher_id" id="publisher_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="">- Pilih Penerbit -</option>
                @foreach ($publishers as $publisher)
                    <option value="{{ $publisher->id }}" @selected((int) old('publisher_id', $book->publisher_id ?? 0) === $publisher->id)>
                        {{ $publisher->name }}
                    </option>
                @endforeach
            </select>
            @error('publisher_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="published_year" class="block text-sm font-medium text-gray-700">Tahun Terbit</label>
            <input type="number" name="published_year" id="published_year"
                   value="{{ old('published_year', $book->published_year ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('published_year') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="language" class="block text-sm font-medium text-gray-700">Bahasa</label>
            <input type="text" name="language" id="language" value="{{ old('language', $book->language ?? 'Indonesia') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('language') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="stock" class="block text-sm font-medium text-gray-700">Stok *</label>
            <input type="number" name="stock" id="stock" min="0" value="{{ old('stock', $book->stock ?? 0) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @error('stock') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700">Batas Stok Menipis</label>
            <input type="number" name="low_stock_threshold" id="low_stock_threshold" min="0"
                   value="{{ old('low_stock_threshold', $book->low_stock_threshold ?? 2) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('low_stock_threshold') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       @checked((bool) old('is_active', $book->is_active ?? true))
                       class="rounded border-gray-300">
                Buku aktif (boleh dipinjam)
            </label>
            @error('is_active') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
            <textarea name="description" id="description" rows="4"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $book->description ?? '') }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <span class="block text-sm font-medium text-gray-700">Kategori (Many-to-Many, pivot added_at)</span>
            <div class="mt-2 grid grid-cols-1 gap-2 rounded-md border border-gray-200 p-3 md:grid-cols-2">
                @foreach ($categories as $category)
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                               @checked(in_array($category->id, $selected, true))
                               class="rounded border-gray-300">
                        <span class="font-medium">{{ $category->name }}</span>
                    </label>

                    @foreach ($category->children as $child)
                        <label class="ml-5 inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="category_ids[]" value="{{ $child->id }}"
                                   @checked(in_array($child->id, $selected, true))
                                   class="rounded border-gray-300">
                            — {{ $child->name }}
                        </label>
                    @endforeach
                @endforeach
            </div>
            @error('category_ids') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('category_ids.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="mt-6 flex items-center gap-3">
        <button type="submit" class="rounded bg-blue-600 px-5 py-2 font-bold text-white hover:bg-blue-700">
            {{ $editing ? 'Perbarui Buku' : 'Simpan Buku' }}
        </button>
        <a href="{{ route('books.index') }}" class="rounded bg-gray-200 px-5 py-2 font-semibold text-gray-700 hover:bg-gray-300">
            Batal
        </a>
    </div>
</form>
