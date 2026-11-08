<?php

use App\Models\User;
use Modules\Catalog\Models\Book;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Services\LoanService;
use Modules\Membership\Models\Member;

test('buku dapat di-soft-delete dan direstore', function () {
    $book = Book::factory()->create();

    $book->delete();

    expect(Book::count())->toBe(0)
        ->and(Book::withTrashed()->count())->toBe(1)
        ->and(Book::withTrashed()->find($book->id)?->trashed())->toBeTrue();

    $book->restore();

    expect(Book::count())->toBe(1)
        ->and(Book::withTrashed()->count())->toBe(1);
});

test('anggota dapat di-soft-delete dan direstore', function () {
    $member = Member::factory()->create();

    $member->delete();

    expect(Member::count())->toBe(0)
        ->and(Member::withTrashed()->count())->toBe(1)
        ->and(Member::withTrashed()->find($member->id)?->trashed())->toBeTrue();

    $member->restore();

    expect(Member::count())->toBe(1);
});

test('peminjaman dapat di-soft-delete dan direstore', function () {
    $book = Book::factory()->create(['stock' => 5]);
    $member = Member::factory()->create();

    $loan = app(LoanService::class)->borrow(
        $member->id,
        [['book_id' => $book->id, 'quantity' => 1]],
    );

    $loan->delete();

    expect(Loan::count())->toBe(0)
        ->and(Loan::withTrashed()->count())->toBe(1)
        ->and(Loan::withTrashed()->find($loan->id)?->trashed())->toBeTrue();

    $loan->restore();

    expect(Loan::count())->toBe(1);
});

test('admin dapat merestore buku lewat route', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $book = Book::factory()->create();
    $book->delete();

    $this->actingAs($admin)
        ->post(route('books.restore', $book->id))
        ->assertRedirect(route('books.index'));

    expect(Book::find($book->id))->not->toBeNull();
});

test('admin dapat merestore anggota lewat route', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = Member::factory()->create();
    $member->delete();

    $this->actingAs($admin)
        ->post(route('members.restore', $member->id))
        ->assertRedirect(route('members.index'));

    expect(Member::find($member->id))->not->toBeNull();
});

test('user biasa tidak dapat mengakses form tambah buku', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get(route('books.create'))
        ->assertForbidden();
});

test('user biasa tetap dapat melihat daftar buku', function () {
    $user = User::factory()->create(['role' => 'user']);
    Book::factory()->count(3)->create();

    $this->actingAs($user)
        ->get(route('books.index'))
        ->assertOk()
        ->assertSee('Katalog Buku');
});
