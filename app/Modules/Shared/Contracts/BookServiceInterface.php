<?php

namespace Modules\Shared\Contracts;

/**
 * Contract lintas bounded context: Catalog -> Circulation.
 *
 * Circulation tidak boleh mengakses model Book secara langsung.
 * Semua kebutuhan data/stok buku harus melalui contract ini.
 */
interface BookServiceInterface
{
    /**
     * Ambil data buku yang dibutuhkan untuk membuat detail peminjaman.
     *
     * @return array{id:int,title:string,isbn:string,stock:int}|null
     */
    public function getBookForLoan(int $bookId): ?array;

    /**
     * Cek apakah stok buku mencukupi untuk jumlah yang diminta.
     */
    public function isStockAvailable(int $bookId, int $quantity): bool;

    /**
     * Kurangi stok buku secara atomik (row lock).
     */
    public function reduceStock(int $bookId, int $quantity): bool;

    /**
     * Tambah stok buku saat pengembalian.
     */
    public function increaseStock(int $bookId, int $quantity): bool;

    /**
     * Daftar buku yang tersedia untuk form peminjaman.
     *
     * @return array<int,array{id:int,title:string,isbn:string,stock:int,author_name:string|null}>
     */
    public function getAvailableBooks(): array;
}
