<?php

namespace Modules\Circulation\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Circulation\Events\BookBorrowed;
use Modules\Circulation\Events\BookReturned;
use Modules\Circulation\Models\Fine;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Models\LoanItem;
use Modules\Circulation\Models\LoanStatusHistory;
use Modules\Circulation\ValueObjects\Denda;
use Modules\Shared\Contracts\BookServiceInterface;
use Modules\Shared\Contracts\MemberServiceInterface;
use Modules\Shared\Exceptions\BookNotAvailableException;
use Modules\Shared\Exceptions\InsufficientStockException;
use Modules\Shared\Exceptions\LoanAlreadyReturnedException;
use Modules\Shared\Exceptions\MemberNotEligibleException;

class LoanService
{
    public const LOAN_DAYS = 7;

    public function __construct(
        private BookServiceInterface $bookService,
        private MemberServiceInterface $memberService,
    ) {}

    /**
     * Membuat peminjaman secara atomik.
     *
     * Semua langkah (buat loan, snapshot item, kurangi stok, update status
     * anggota, catat history, dispatch event) berada dalam satu transaksi.
     * Jika satu langkah gagal, seluruh perubahan di-rollback.
     *
     * @param  array<int,array{book_id:int|string,quantity:int|string}>  $items
     */
    public function borrow(int $memberId, array $items, ?string $notes = null, ?int $changedBy = null): Loan
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Minimal satu buku harus dipinjam.');
        }

        $changedBy = $changedBy ?? auth()->id();

        return DB::transaction(function () use ($memberId, $items, $notes, $changedBy) {
            $member = $this->memberService->getMemberForLoan($memberId);

            if (! $member) {
                throw new MemberNotEligibleException('Anggota tidak ditemukan.');
            }

            if (! $this->memberService->isEligibleToBorrow($memberId)) {
                $reason = $member['active_loans'] >= $member['max_loans']
                    ? "Anggota '{$member['name']}' sudah mencapai batas peminjaman ({$member['max_loans']} buku)."
                    : "Anggota '{$member['name']}' berstatus '{$member['status']}' dan tidak dapat meminjam.";

                throw new MemberNotEligibleException($reason);
            }

            $now = now();

            $loan = Loan::create([
                'member_id' => $memberId,
                'member_name' => $member['name'],
                'loan_number' => $this->generateLoanNumber(),
                'borrowed_at' => $now,
                'due_at' => $now->copy()->addDays(self::LOAN_DAYS),
                'status' => Loan::STATUS_BORROWED,
                'total_fine' => 0,
                'notes' => $notes,
            ]);

            foreach ($items as $item) {
                $bookId = (int) ($item['book_id'] ?? 0);
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity < 1) {
                    throw new InvalidArgumentException('Jumlah buku minimal 1.');
                }

                // Baca data buku lewat contract Catalog.
                $book = $this->bookService->getBookForLoan($bookId);

                if (! $book) {
                    throw new BookNotAvailableException($bookId);
                }

                if (! $this->bookService->isStockAvailable($bookId, $quantity)) {
                    throw new InsufficientStockException($book['title'], $book['stock'], $quantity);
                }

                // Kurangi stok lewat contract (row lock di dalam service Catalog).
                if (! $this->bookService->reduceStock($bookId, $quantity)) {
                    throw new InsufficientStockException($book['title'], $book['stock'], $quantity);
                }

                // Snapshot data buku: judul & ISBN tidak akan berubah walau buku diubah.
                LoanItem::create([
                    'loan_id' => $loan->id,
                    'book_id' => $book['id'],
                    'book_title' => $book['title'],
                    'book_isbn' => $book['isbn'],
                    'quantity' => $quantity,
                ]);
            }

            // Update status anggota lewat contract Membership.
            $this->memberService->markAsBorrowing($memberId);

            LoanStatusHistory::create([
                'loan_id' => $loan->id,
                'status' => Loan::STATUS_BORROWED,
                'notes' => $notes ?? 'Peminjaman dibuat.',
                'changed_by' => $changedBy,
            ]);

            $loan->load('items');

            // Domain event setelah peminjaman berhasil.
            event(new BookBorrowed($loan));

            return $loan->load(['items', 'fines', 'statusHistories']);
        });
    }

    /**
     * Mengembalikan buku secara atomik.
     *
     * Stok dikembalikan, denda dihitung otomatis oleh Value Object Denda,
     * status anggota diperbarui, dan event BookReturned ditembakkan.
     */
    public function returnBook(int $loanId, ?string $notes = null, ?int $changedBy = null): Loan
    {
        $changedBy = $changedBy ?? auth()->id();

        return DB::transaction(function () use ($loanId, $notes, $changedBy) {
            $loan = Loan::with('items')->findOrFail($loanId);

            if ($loan->isReturned()) {
                throw new LoanAlreadyReturnedException($loan->loan_number);
            }

            $returnedAt = now();

            // Kembalikan stok setiap buku lewat contract Catalog.
            foreach ($loan->items as $item) {
                $this->bookService->increaseStock((int) $item->book_id, (int) $item->quantity);
            }

            // Perhitungan denda otomatis.
            $denda = Denda::between($loan->due_at, $returnedAt);

            if ($denda->isLate()) {
                Fine::create([
                    'loan_id' => $loan->id,
                    'member_id' => $loan->member_id,
                    'days_late' => $denda->getDaysLate(),
                    'amount' => $denda->getAmount(),
                    'status' => 'unpaid',
                ]);
            }

            $loan->update([
                'status' => Loan::STATUS_RETURNED,
                'returned_at' => $returnedAt,
                'total_fine' => $denda->getAmount(),
            ]);

            // Update status anggota lewat contract Membership.
            $this->memberService->markAsReturned((int) $loan->member_id);

            LoanStatusHistory::create([
                'loan_id' => $loan->id,
                'status' => Loan::STATUS_RETURNED,
                'notes' => $notes ?? ('Buku dikembalikan. Denda: ' . (string) $denda),
                'changed_by' => $changedBy,
            ]);

            $fresh = $loan->fresh(['items']);
            event(new BookReturned($fresh, $denda));

            return $loan->fresh(['items', 'fines', 'statusHistories']);
        });
    }

    public function payFine(Fine $fine): Fine
    {
        if (! $fine->isPaid()) {
            $fine->markAsPaid();
        }

        return $fine->fresh();
    }

    private function generateLoanNumber(): string
    {
        do {
            $number = 'LN-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Loan::withTrashed()->where('loan_number', $number)->exists());

        return $number;
    }
}
