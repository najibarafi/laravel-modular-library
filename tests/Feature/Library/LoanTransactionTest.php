<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Modules\Catalog\Models\Book;
use Modules\Circulation\Events\BookBorrowed;
use Modules\Circulation\Models\Fine;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Models\LoanItem;
use Modules\Circulation\Services\LoanService;
use Modules\Membership\Models\Member;
use Modules\Shared\Exceptions\InsufficientStockException;
use Modules\Shared\Exceptions\LoanAlreadyReturnedException;
use Modules\Shared\Exceptions\MemberNotEligibleException;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-11-07 10:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

test('peminjaman berhasil mengurangi stok, mencatat detail, dan menembakkan event', function () {
    Event::fake([BookBorrowed::class]);

    $book = Book::factory()->create(['stock' => 5]);
    $member = Member::factory()->create([
        'status' => Member::STATUS_ACTIVE,
        'active_loans' => 0,
        'max_loans' => 3,
    ]);

    $loan = app(LoanService::class)->borrow(
        $member->id,
        [['book_id' => $book->id, 'quantity' => 2]],
        'Test peminjaman',
    );

    expect($loan->status)->toBe(Loan::STATUS_BORROWED)
        ->and($loan->member_name)->toBe($member->name)
        ->and($loan->items)->toHaveCount(1)
        ->and($loan->items->first()->book_title)->toBe($book->title)
        ->and($loan->items->first()->book_isbn)->toBe($book->isbn)
        ->and($loan->items->first()->quantity)->toBe(2);

    expect($book->fresh()->stock)->toBe(3);
    expect($member->fresh()->active_loans)->toBe(1);
    expect($member->fresh()->status)->toBe(Member::STATUS_BORROWING);
    expect(Loan::count())->toBe(1);
    expect(LoanItem::count())->toBe(1);

    Event::assertDispatched(
        BookBorrowed::class,
        fn (BookBorrowed $event) => $event->loan->id === $loan->id,
    );
});

test('transaksi rollback total jika stok tidak mencukupi', function () {
    $book = Book::factory()->create(['stock' => 1]);
    $member = Member::factory()->create([
        'status' => Member::STATUS_ACTIVE,
        'active_loans' => 0,
        'max_loans' => 3,
    ]);

    expect(fn () => app(LoanService::class)->borrow(
        $member->id,
        [['book_id' => $book->id, 'quantity' => 3]],
    ))->toThrow(InsufficientStockException::class);

    expect(Loan::count())->toBe(0)
        ->and(LoanItem::count())->toBe(0)
        ->and($book->fresh()->stock)->toBe(1)
        ->and($member->fresh()->active_loans)->toBe(0)
        ->and($member->fresh()->status)->toBe(Member::STATUS_ACTIVE);
});

test('pengembalian terlambat menghitung denda otomatis dan mengembalikan stok', function () {
    $book = Book::factory()->create(['stock' => 5]);
    $member = Member::factory()->create([
        'status' => Member::STATUS_ACTIVE,
        'active_loans' => 0,
        'max_loans' => 3,
    ]);

    $loan = app(LoanService::class)->borrow(
        $member->id,
        [['book_id' => $book->id, 'quantity' => 1]],
    );

    expect($book->fresh()->stock)->toBe(4);

    // Buat peminjaman terlambat 3 hari.
    $loan->update(['due_at' => now()->copy()->subDays(3)]);

    $returned = app(LoanService::class)->returnBook($loan->id);

    expect($returned->status)->toBe(Loan::STATUS_RETURNED)
        ->and($returned->returned_at?->toDateTimeString())->toBe('2026-11-07 10:00:00')
        ->and((float) $returned->total_fine)->toBe(3000.0)
        ->and($returned->fines)->toHaveCount(1)
        ->and($returned->fines->first()->days_late)->toBe(3)
        ->and((float) $returned->fines->first()->amount)->toBe(3000.0);

    expect($book->fresh()->stock)->toBe(5);
    expect($member->fresh()->active_loans)->toBe(0);
    expect($member->fresh()->status)->toBe(Member::STATUS_ACTIVE);
    expect(Fine::count())->toBe(1);
});

test('anggota yang ditangguhkan tidak dapat meminjam', function () {
    $book = Book::factory()->create(['stock' => 5]);
    $member = Member::factory()->create(['status' => Member::STATUS_SUSPENDED]);

    expect(fn () => app(LoanService::class)->borrow(
        $member->id,
        [['book_id' => $book->id, 'quantity' => 1]],
    ))->toThrow(MemberNotEligibleException::class);

    expect(Loan::count())->toBe(0);
    expect($book->fresh()->stock)->toBe(5);
});

test('peminjaman yang sudah dikembalikan tidak dapat dikembalikan dua kali', function () {
    $book = Book::factory()->create(['stock' => 5]);
    $member = Member::factory()->create();

    $loan = app(LoanService::class)->borrow(
        $member->id,
        [['book_id' => $book->id, 'quantity' => 1]],
    );

    app(LoanService::class)->returnBook($loan->id);

    expect(fn () => app(LoanService::class)->returnBook($loan->id))
        ->toThrow(LoanAlreadyReturnedException::class);

    expect($book->fresh()->stock)->toBe(5);
});
