<?php

namespace Modules\Catalog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Catalog\Models\Publisher;

class PublisherController extends Controller
{
    public function index()
    {
        $publishers = Publisher::withCount('books')->orderBy('name')->paginate(15);

        return view('catalog.publishers.index', compact('publishers'));
    }

    public function create()
    {
        return view('catalog.publishers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:publishers,slug'],
            'address' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name']);
        $publisher = Publisher::create($data);

        return redirect()
            ->route('publishers.index')
            ->with('success', "Penerbit '{$publisher->name}' berhasil ditambahkan.");
    }

    public function edit(Publisher $publisher)
    {
        return view('catalog.publishers.edit', compact('publisher'));
    }

    public function update(Request $request, Publisher $publisher)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('publishers', 'slug')->ignore($publisher->id)],
            'address' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $data['slug'] = $this->uniqueSlug(
            $data['slug'] ?? null,
            $data['name'] ?? $publisher->name,
            $publisher->id
        );

        $publisher->update($data);

        return redirect()
            ->route('publishers.index')
            ->with('success', "Penerbit '{$publisher->name}' berhasil diperbarui.");
    }

    public function destroy(Publisher $publisher)
    {
        if ($publisher->books()->exists()) {
            return back()->withErrors([
                'publisher' => "Penerbit '{$publisher->name}' masih memiliki buku dan tidak dapat dihapus.",
            ]);
        }

        $publisher->delete();

        return redirect()
            ->route('publishers.index')
            ->with('success', "Penerbit '{$publisher->name}' berhasil dihapus.");
    }

    private function uniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name) ?: Str::random(8);
        $candidate = $base;
        $counter = 2;

        while (
            Publisher::where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base . '-' . $counter++;
        }

        return $candidate;
    }
}
