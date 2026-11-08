<?php

namespace Modules\Catalog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Author;
use Modules\Catalog\Models\Book;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Publisher;
use Modules\Catalog\Requests\StoreBookRequest;
use Modules\Catalog\Requests\UpdateBookRequest;

class BookController extends Controller
{
    /**
     * Daftar buku dengan eager loading untuk menghindari N+1.
     */
    public function index(Request $request)
    {
        $query = $request->boolean('trashed')
            ? Book::onlyTrashed()
            : Book::query();

        $query->with(['author', 'publisher', 'categories']);

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($request->filled('category_id')) {
            $query->inCategory((int) $request->input('category_id'));
        }

        if ($request->filled('author_id')) {
            $query->byAuthor((int) $request->input('author_id'));
        }

        if ($request->filled('publisher_id')) {
            $query->byPublisher((int) $request->input('publisher_id'));
        }

        if ($request->boolean('available')) {
            $query->available();
        }

        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        $books = $query->latest()->paginate(10)->withQueryString();

        // Data untuk filter. Eager load children agar dropdown hierarki tidak N+1.
        $categories = Category::with('children')->roots()->orderBy('name')->get();
        $authors = Author::orderBy('name')->get();
        $publishers = Publisher::orderBy('name')->get();

        return view('catalog.books.index', compact('books', 'categories', 'authors', 'publishers'));
    }

    public function create()
    {
        $authors = Author::orderBy('name')->get();
        $publishers = Publisher::orderBy('name')->get();
        $categories = Category::with('children')->orderBy('name')->get();

        return view('catalog.books.create', compact('authors', 'publishers', 'categories'));
    }

    public function store(StoreBookRequest $request)
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['title']);
        $data['is_active'] = $data['is_active'] ?? true;

        $book = DB::transaction(function () use ($data, $categoryIds) {
            $book = Book::create($data);
            $this->syncCategories($book, $categoryIds);

            return $book;
        });

        return redirect()
            ->route('books.index')
            ->with('success', "Buku '{$book->title}' berhasil ditambahkan.");
    }

    public function show(Book $book)
    {
        $book->load([
            'author',
            'publisher',
            'categories.parent',
            'categories.children',
        ]);

        return view('catalog.books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $authors = Author::orderBy('name')->get();
        $publishers = Publisher::orderBy('name')->get();
        $categories = Category::with('children')->orderBy('name')->get();
        $selectedCategories = $book->categories()->pluck('categories.id')->all();

        return view('catalog.books.edit', compact('book', 'authors', 'publishers', 'categories', 'selectedCategories'));
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $data = $request->validated();
        $hasCategories = array_key_exists('category_ids', $data);
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $data['slug'] = $this->uniqueSlug(
            $data['slug'] ?? null,
            $data['title'] ?? $book->title,
            $book->id
        );

        DB::transaction(function () use ($book, $data, $hasCategories, $categoryIds) {
            $book->update($data);

            if ($hasCategories) {
                $this->syncCategories($book, $categoryIds);
            }
        });

        return redirect()
            ->route('books.index')
            ->with('success', "Buku '{$book->title}' berhasil diperbarui.");
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', "Buku '{$book->title}' berhasil dihapus (soft delete).");
    }

    /**
     * Restore buku yang di-soft delete (khusus admin).
     */
    public function restore(int $id)
    {
        $book = Book::withTrashed()->findOrFail($id);
        $book->restore();

        return redirect()
            ->route('books.index')
            ->with('success', "Buku '{$book->title}' berhasil dipulihkan.");
    }

    private function uniqueSlug(?string $slug, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $title) ?: Str::random(8);
        $candidate = $base;
        $counter = 2;

        while (
            Book::withTrashed()
                ->where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base . '-' . $counter++;
        }

        return $candidate;
    }

    /**
     * Sinkronisasi pivot Buku <-> Kategori sambil mempertahankan added_at lama.
     */
    private function syncCategories(Book $book, array $categoryIds): void
    {
        $categoryIds = array_map('intval', $categoryIds);

        $existing = $book->categories()
            ->get()
            ->mapWithKeys(fn (Category $category) => [
                $category->id => $category->pivot->added_at,
            ]);

        $sync = [];

        foreach ($categoryIds as $categoryId) {
            $sync[$categoryId] = [
                'added_at' => $existing->get($categoryId) ?? now(),
            ];
        }

        $book->categories()->sync($sync);
    }
}
