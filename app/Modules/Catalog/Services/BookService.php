<?php

namespace Modules\Catalog\Services;

use Modules\Catalog\Models\Book;
use Modules\Shared\Contracts\BookServiceInterface;

/**
 * Implementasi contract Catalog Context.
 *
 * Service inilah satu-satunya pintu bagi context lain (Circulation)
 * untuk membaca data buku dan mengubah stok.
 */
class BookService implements BookServiceInterface
{
    public function getBookForLoan(int $bookId): ?array
    {
        $book = Book::find($bookId);

        if (! $book || ! $book->is_active) {
            return null;
        }

        return [
            'id' => $book->id,
            'title' => $book->title,
            'isbn' => $book->isbn,
            'stock' => $book->stock,
        ];
    }

    public function isStockAvailable(int $bookId, int $quantity): bool
    {
        $book = Book::find($bookId);

        return $book !== null
            && $book->is_active
            && $book->isInStock($quantity);
    }

    public function reduceStock(int $bookId, int $quantity): bool
    {
        // lockForUpdate mencegah race condition saat dua peminjaman bersamaan.
        $book = Book::lockForUpdate()->find($bookId);

        if (! $book || ! $book->is_active || ! $book->isInStock($quantity)) {
            return false;
        }

        $book->decrement('stock', $quantity);

        return true;
    }

    public function increaseStock(int $bookId, int $quantity): bool
    {
        // withTrashed: buku yang sudah di-soft-delete tetap bisa dikembalikan stoknya.
        $book = Book::withTrashed()->lockForUpdate()->find($bookId);

        if (! $book) {
            return false;
        }

        $book->increment('stock', $quantity);

        return true;
    }

    public function getAvailableBooks(): array
    {
        return Book::available()
            ->with('author')
            ->orderBy('title')
            ->get()
            ->map(fn (Book $book) => [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'stock' => $book->stock,
                'author_name' => $book->author?->name,
            ])
            ->all();
    }
}
