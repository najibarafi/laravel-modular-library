<?php

namespace Modules\Catalog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Catalog\Models\Author;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::withCount('books')->orderBy('name')->paginate(15);

        return view('catalog.authors.index', compact('authors'));
    }

    public function create()
    {
        return view('catalog.authors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:authors,slug'],
            'bio' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name']);
        $author = Author::create($data);

        return redirect()
            ->route('authors.index')
            ->with('success', "Penulis '{$author->name}' berhasil ditambahkan.");
    }

    public function edit(Author $author)
    {
        return view('catalog.authors.edit', compact('author'));
    }

    public function update(Request $request, Author $author)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('authors', 'slug')->ignore($author->id)],
            'bio' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['slug'] = $this->uniqueSlug(
            $data['slug'] ?? null,
            $data['name'] ?? $author->name,
            $author->id
        );

        $author->update($data);

        return redirect()
            ->route('authors.index')
            ->with('success', "Penulis '{$author->name}' berhasil diperbarui.");
    }

    public function destroy(Author $author)
    {
        if ($author->books()->exists()) {
            return back()->withErrors([
                'author' => "Penulis '{$author->name}' masih memiliki buku dan tidak dapat dihapus.",
            ]);
        }

        $author->delete();

        return redirect()
            ->route('authors.index')
            ->with('success', "Penulis '{$author->name}' berhasil dihapus.");
    }

    private function uniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name) ?: Str::random(8);
        $candidate = $base;
        $counter = 2;

        while (
            Author::where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base . '-' . $counter++;
        }

        return $candidate;
    }
}
