<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Book;
use Modules\Circulation\Models\Loan;

class QueryOptimizationDemo extends Command
{
    protected $signature = 'library:query-demo';

    protected $description = 'Membandingkan jumlah query N+1 vs eager loading pada data perpustakaan';

    public function handle(): int
    {
        $this->info('=== DEMO OPTIMASI QUERY PERPUSTAKAAN ===');
        $this->line('Data saat ini: ' . Loan::count() . ' peminjaman, ' . Book::count() . ' buku.');
        $this->newLine();

        /*
        |--------------------------------------------------------------------------
        | 1. Peminjaman -> DetailPeminjaman (HasMany)
        |--------------------------------------------------------------------------
        */

        DB::flushQueryLog();
        DB::enableQueryLog();

        $loans = Loan::all();
        $totalItems = 0;

        foreach ($loans as $loan) {
            $totalItems += $loan->items->count();
        }

        $nPlusOneLoanQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $loans = Loan::with('items')->get();
        $totalItemsEager = 0;

        foreach ($loans as $loan) {
            $totalItemsEager += $loan->items->count();
        }

        $eagerLoanQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        /*
        |--------------------------------------------------------------------------
        | 2. Buku -> Penulis + Kategori (BelongsTo + BelongsToMany)
        |--------------------------------------------------------------------------
        */

        DB::flushQueryLog();
        DB::enableQueryLog();

        $books = Book::all();
        $bookLabels = [];

        foreach ($books as $book) {
            $bookLabels[] = $book->title
                . ' oleh ' . ($book->author?->name ?? '-')
                . ' [' . $book->categories->pluck('name')->implode(', ') . ']';
        }

        $nPlusOneBookQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $books = Book::with(['author', 'categories'])->get();
        $bookLabelsEager = [];

        foreach ($books as $book) {
            $bookLabelsEager[] = $book->title
                . ' oleh ' . ($book->author?->name ?? '-')
                . ' [' . $book->categories->pluck('name')->implode(', ') . ']';
        }

        $eagerBookQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        /*
        |--------------------------------------------------------------------------
        | Output
        |--------------------------------------------------------------------------
        */

        $this->table(
            ['Skenario', 'Jumlah Query', 'Keterangan'],
            [
                [
                    'Peminjaman tanpa eager loading',
                    $nPlusOneLoanQueries,
                    "1 query loans + {$loans->count()} query items (N+1)",
                ],
                [
                    'Peminjaman dengan with(items)',
                    $eagerLoanQueries,
                    '1 query loans + 1 query items',
                ],
                [
                    'Buku tanpa eager loading',
                    $nPlusOneBookQueries,
                    '1 query books + N query author + N query pivot/categories',
                ],
                [
                    'Buku dengan with(author, categories)',
                    $eagerBookQueries,
                    '1 query books + 1 query authors + 1 query categories',
                ],
            ],
        );

        $this->newLine();
        $this->info("Total item peminjaman (N+1)      : {$totalItems}");
        $this->info("Total item peminjaman (eager)     : {$totalItemsEager}");
        $this->info('Total label buku (N+1/eager)      : ' . count($bookLabels) . '/' . count($bookLabelsEager));
        $this->newLine();
        $this->comment('Kesimpulan: eager loading memangkas jumlah query dari O(N) menjadi O(1) per relasi.');

        return self::SUCCESS;
    }
}
