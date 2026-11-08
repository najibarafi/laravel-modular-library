<?php

namespace Modules\Catalog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Requests\StoreCategoryRequest;
use Modules\Catalog\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        // Eager loading parent + children + hitung buku untuk menghindari N+1.
        $categories = ($request->boolean('trashed') ? Category::onlyTrashed() : Category::query())
            ->with(['parent', 'children'])
            ->withCount('books')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('catalog.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::orderBy('name')->get();

        return view('catalog.categories.create', compact('parents'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name']);

        $category = Category::create($data);

        return redirect()
            ->route('categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil ditambahkan.");
    }

    public function edit(Category $category)
    {
        $excludedIds = array_merge([$category->id], $this->descendantIds($category));
        $parents = Category::whereNotIn('id', $excludedIds)->orderBy('name')->get();

        return view('catalog.categories.edit', compact('category', 'parents'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        if (! empty($data['parent_id']) && in_array((int) $data['parent_id'], $this->descendantIds($category), true)) {
            return back()
                ->withInput()
                ->withErrors(['parent_id' => 'Kategori induk tidak boleh berasal dari sub-kategori sendiri.']);
        }

        $data['slug'] = $this->uniqueSlug(
            $data['slug'] ?? null,
            $data['name'] ?? $category->name,
            $category->id
        );

        $category->update($data);

        return redirect()
            ->route('categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    public function destroy(Category $category)
    {
        if ($category->children()->exists()) {
            return back()->withErrors([
                'category' => "Kategori '{$category->name}' masih memiliki sub-kategori dan tidak dapat dihapus.",
            ]);
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil dihapus (soft delete).");
    }

    public function restore(int $id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();

        return redirect()
            ->route('categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil dipulihkan.");
    }

    private function uniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name) ?: Str::random(8);
        $candidate = $base;
        $counter = 2;

        while (
            Category::withTrashed()
                ->where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base . '-' . $counter++;
        }

        return $candidate;
    }

    /**
     * Kumpulkan ID seluruh turunan kategori (untuk mencegah siklus parent).
     */
    private function descendantIds(Category $category): array
    {
        $ids = [];

        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->descendantIds($child));
        }

        return $ids;
    }
}
