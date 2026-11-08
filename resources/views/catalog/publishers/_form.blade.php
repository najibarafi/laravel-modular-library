@php($editing = isset($publisher) && $publisher)

<form action="{{ $editing ? route('publishers.update', $publisher) : route('publishers.store') }}" method="POST">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="name" class="block text-sm font-medium text-gray-700">Nama Penerbit *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $publisher->name ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $publisher->slug ?? '') }}"
                   placeholder="Kosongkan untuk otomatis dari nama"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $publisher->email ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Telepon</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $publisher->phone ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
            <textarea name="address" id="address" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('address', $publisher->address ?? '') }}</textarea>
            @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="mt-6 flex items-center gap-3">
        <button type="submit" class="rounded bg-blue-600 px-5 py-2 font-bold text-white hover:bg-blue-700">
            {{ $editing ? 'Perbarui Penerbit' : 'Simpan Penerbit' }}
        </button>
        <a href="{{ route('publishers.index') }}" class="rounded bg-gray-200 px-5 py-2 font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
    </div>
</form>
