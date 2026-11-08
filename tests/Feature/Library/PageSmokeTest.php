<?php

use App\Models\User;
use Modules\Catalog\Models\Book;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Services\LoanService;
use Modules\Membership\Models\Member;

test('semua halaman utama perpustakaan dapat diakses admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);
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

    // Buat denda agar halaman denda juga merender baris data.
    $loan->update(['due_at' => now()->copy()->subDays(2)]);
    app(LoanService::class)->returnBook($loan->id);

    $urls = [
        route('dashboard'),
        route('admin.dashboard'),
        route('books.index'),
        route('books.index', ['trashed' => 1]),
        route('books.show', $book),
        route('books.create'),
        route('books.edit', $book),
        route('categories.index'),
        route('categories.index', ['trashed' => 1]),
        route('categories.create'),
        route('authors.index'),
        route('authors.create'),
        route('publishers.index'),
        route('publishers.create'),
        route('members.index'),
        route('members.index', ['trashed' => 1]),
        route('members.show', $member),
        route('members.create'),
        route('members.edit', $member),
        route('roles.index'),
        route('roles.create'),
        route('loans.index'),
        route('loans.index', ['trashed' => 1]),
        route('loans.show', $loan),
        route('loans.create'),
        route('fines.index'),
    ];

    foreach ($urls as $url) {
        $this->actingAs($admin)
            ->get($url)
            ->assertOk();
    }
});

test('halaman daftar dapat diakses user biasa tanpa aksi tulis', function () {
    $user = User::factory()->create(['role' => 'user']);
    Book::factory()->count(2)->create();

    $this->actingAs($user)->get(route('books.index'))->assertOk();
    $this->actingAs($user)->get(route('books.create'))->assertForbidden();
    $this->actingAs($user)->get(route('members.index'))->assertOk();
});
