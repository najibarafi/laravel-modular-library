# Optimasi Query: Eager Loading, Scope, dan Pengukuran N+1

Dokumen ini menjelaskan strategi optimasi query pada Sistem Perpustakaan, hasil pengukuran
sebelum/sesudah, serta bukti otomatis melalui test.

---

## 1. Masalah N+1

N+1 terjadi ketika aplikasi mengambil `N` baris utama dengan 1 query, lalu untuk setiap baris
mengakses relasi sehingga menambah `N` query baru. Contoh:

```php
// N+1: 1 query loans + N query loan_items
$loans = Loan::all();
foreach ($loans as $loan) {
    echo $loan->items->count();
}
```

Pada data demo (11 peminjaman, 40 buku), pola di atas menghasilkan:

- **12 query** untuk daftar peminjaman beserta item (1 + 11).
- **81 query** untuk daftar buku beserta penulis dan kategori (1 + 40 + 40).

Solusinya adalah **eager loading** dengan `with()`:

```php
// Eager: 1 query loans + 1 query loan_items
$loans = Loan::with('items')->get();

// Eager: 1 query books + 1 query authors + 1 query categories
$books = Book::with(['author', 'categories'])->get();
```

---

## 2. Hasil Pengukuran Sebelum vs Sesudah

Perintah bukti:

```bash
php artisan library:query-demo
```

Output aktual pada data demo:

```text
=== DEMO OPTIMASI QUERY PERPUSTAKAAN ===
Data saat ini: 11 peminjaman, 40 buku.

+--------------------------------------+--------------+-----------------------------------------------------------+
| Skenario                             | Jumlah Query | Keterangan                                                |
+--------------------------------------+--------------+-----------------------------------------------------------+
| Peminjaman tanpa eager loading       | 12           | 1 query loans + 11 query items (N+1)                      |
| Peminjaman dengan with(items)        | 2            | 1 query loans + 1 query items                             |
| Buku tanpa eager loading             | 81           | 1 query books + N query author + N query pivot/categories |
| Buku dengan with(author, categories) | 3            | 1 query books + 1 query authors + 1 query categories      |
+--------------------------------------+--------------+-----------------------------------------------------------+

Kesimpulan: eager loading memangkas jumlah query dari O(N) menjadi O(1) per relasi.
```

Ringkasan:

| Skenario | Tanpa eager loading | Dengan eager loading | Selisih |
|---|---:|---:|---:|
| Daftar peminjaman + item | 12 | 2 | **83% lebih sedikit** |
| Daftar buku + penulis + kategori | 81 | 3 | **96% lebih sedikit** |

> Catatan: angka pasti bergantung pada jumlah data. Yang penting adalah pertumbuhannya:
> tanpa eager loading jumlah query naik linear terhadap `N`, sedangkan dengan eager loading
> jumlah query tetap konstan (satu query per relasi).

---

## 3. Penerapan Eager Loading di Controller

| Controller | Relasi yang di-eager load | Tujuan |
|---|---|---|
| `BookController@index` | `author`, `publisher`, `categories` | Tabel katalog tidak melakukan query per baris |
| `BookController@create/edit` | `Category::with('children')` | Menampilkan hierarki kategori tanpa query berulang |
| `CategoryController@index` | `parent`, `children` + `withCount('books')` | Kolom induk, sub-kategori, dan jumlah buku |
| `AuthorController@index` | `withCount('books')` | Jumlah buku per penulis |
| `PublisherController@index` | `withCount('books')` | Jumlah buku per penerbit |
| `MemberController@index` | `memberships`, `roles` + `withCount('memberships')` | Status keanggotaan dan role |
| `RoleController@index` | `withCount('members')` | Jumlah anggota per role |
| `LoanController@index` | `items`, `fines`, `member` | Daftar peminjaman + jumlah item + denda |
| `LoanController@show` | `items`, `fines`, `statusHistories`, `member` | Detail lengkap dalam satu kali muat |
| `FineController@index` | `loan`, `member` | Daftar denda + nomor peminjaman + anggota |

Contoh implementasi di `LoanController@index`:

```php
$loans = ($request->boolean('trashed') ? Loan::onlyTrashed() : Loan::query())
    ->with(['items', 'fines', 'member'])
    ->withCount('items')
    ->latest('borrowed_at')
    ->paginate(15)
    ->withQueryString();
```

---

## 4. Query Scope

Scope memindahkan logika filter ke model agar controller tetap ringkas dan query konsisten.

### Book

```php
public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}

public function scopeAvailable(Builder $query): Builder
{
    return $query->active()->where('stock', '>', 0);
}

public function scopeSearch(Builder $query, string $keyword): Builder
{
    return $query->where(function (Builder $q) use ($keyword) {
        $q->where('title', 'like', "%{$keyword}%")
          ->orWhere('isbn', 'like', "%{$keyword}%")
          ->orWhere('description', 'like', "%{$keyword}%");
    });
}

public function scopeInCategory(Builder $query, int $categoryId): Builder
{
    return $query->whereHas('categories', fn (Builder $q) => $q->where('categories.id', $categoryId));
}

public function scopeLowStock(Builder $query): Builder
{
    return $query->whereColumn('stock', '<=', 'low_stock_threshold');
}
```

Scope lain: `byAuthor`, `byPublisher`.

### Category

- `roots()` — hanya kategori tanpa induk.
- `search($keyword)` — pencarian nama/deskripsi.

### Member

- `active()` — status `active` atau `borrowing`.
- `eligible()` — aktif/borrowing, `active_loans < max_loans`.
- `search($keyword)` — nama, nomor keanggotaan, atau email.

### Loan

- `byStatus($status)`
- `overdue()` — status `borrowed` dan `due_at < now()`.
- `recent($days = 30)`
- `forMember($memberId)`
- `search($keyword)` — nomor peminjaman atau nama anggota.

### Fine

- `unpaid()` — status `unpaid`.

---

## 5. Bukti Test Otomatis

File: `tests/Feature/Library/QueryOptimizationTest.php`

```php
test('eager loading membatasi query daftar peminjaman', function () {
    // ... buat 2 peminjaman ...
    DB::enableQueryLog();

    $loans = Loan::with('items')->get();
    foreach ($loans as $loan) {
        $loan->items->count();
    }

    $queryCount = count(DB::getQueryLog());

    expect($loans)->toHaveCount(2)
        ->and($queryCount)->toBeLessThanOrEqual(2);
});

test('eager loading membatasi query daftar buku beserta relasinya', function () {
    Book::factory()->count(5)->create();
    DB::enableQueryLog();

    $books = Book::with(['author', 'categories'])->get();
    foreach ($books as $book) {
        $book->author?->name;
        $book->categories->count();
    }

    $queryCount = count(DB::getQueryLog());

    expect($books)->toHaveCount(5)
        ->and($queryCount)->toBeLessThanOrEqual(3);
});
```

Test juga memverifikasi scope `available`, `search`, dan `inCategory`.

---

## 6. Cara Menjalankan Ulang

```bash
php artisan migrate:fresh --seed
php artisan library:query-demo
php artisan test --filter=QueryOptimization
```

File implementasi command: `app/Console/Commands/QueryOptimizationDemo.php`.
