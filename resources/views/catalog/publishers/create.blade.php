<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Tambah Penerbit') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <x-flash />
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @include('catalog.publishers._form', ['publisher' => null])
            </div>
        </div>
    </div>
</x-app-layout>
