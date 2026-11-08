@if (session('success'))
    <div class="mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700" role="alert">
        {{ session('success') }}
    </div>
@endif

@if (session('info'))
    <div class="mb-4 rounded border border-blue-400 bg-blue-100 px-4 py-3 text-blue-700" role="alert">
        {{ session('info') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700" role="alert">
        <ul class="list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
