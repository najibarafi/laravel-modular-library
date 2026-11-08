<?php

use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Book;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Services\LoanService;
use Modules\Membership\Models\Member;

test('eager loading membatasi query daftar peminjaman', function () {
    $member = Member::factory()->create([
        'status' => Member::STATUS_ACTIVE,
        'active_loans' => 0,
        'max_loans' => 3,
    ]);
    $book = Book::factory()->create(['stock' => 10]);

    $service = app(LoanService::class);
    $service->borrow($member->id, [['book_id' => $book->id, 'quantity' => 1]]);
    $service->borrow($member->id, [['book_id' => $book->id, 'quantity' => 1]]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $loans = Loan::with('items')->get();

    foreach ($loans as $loan) {
        $loan->items->count();
    }

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($loans)->toHaveCount(2)
        ->and($queryCount)->toBeLessThanOrEqual(2);
});

test('eager loading membatasi query daftar buku beserta relasinya', function () {
    Book::factory()->count(5)->create();

    DB::flushQueryLog();
    DB::enableQueryLog();

    $books = Book::with(['author', 'categories'])->get();

    foreach ($books as $book) {
        $book->author?->name;
        $book->categories->count();
    }

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($books)->toHaveCount(5)
        ->and($queryCount)->toBeLessThanOrEqual(3);
});

test('scope available hanya mengembalikan buku aktif dan bersedia', function () {
    Book::factory()->create(['stock' => 0, 'is_active' => true]);
    Book::factory()->create(['stock' => 5, 'is_active' => false]);
    $available = Book::factory()->create(['stock' => 5, 'is_active' => true]);

    $result = Book::available()->pluck('id');

    expect($result)->toHaveCount(1)
        ->and($result->first())->toBe($available->id);
});

test('scope search dan inCategory memfilter buku', function () {
    $category = Modules\Catalog\Models\Category::factory()->create();
    $target = Book::factory()->create(['title' => 'Laravel Modular Monolith']);
    $target->categories()->attach($category->id, ['added_at' => now()]);
    Book::factory()->create(['title' => 'Buku Lain']);

    $search = Book::search('Modular')->pluck('id');
    $filtered = Book::inCategory($category->id)->pluck('id');

    expect($search->all())->toBe([$target->id])
        ->and($filtered->all())->toBe([$target->id]);
});
