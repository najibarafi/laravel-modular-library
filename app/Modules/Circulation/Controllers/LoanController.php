<?php

namespace Modules\Circulation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Requests\StoreLoanRequest;
use Modules\Circulation\Services\LoanService;
use Modules\Shared\Contracts\BookServiceInterface;
use Modules\Shared\Contracts\MemberServiceInterface;

class LoanController extends Controller
{
    public function __construct(
        private LoanService $loanService,
        private BookServiceInterface $bookService,
        private MemberServiceInterface $memberService,
    ) {}

    /**
     * Eager loading items, fines, dan member untuk menghindari N+1.
     */
    public function index(Request $request)
    {
        $loans = ($request->boolean('trashed') ? Loan::onlyTrashed() : Loan::query())
            ->with(['items', 'fines', 'member'])
            ->when($search = trim((string) $request->input('search')), fn ($q) => $q->search($search))
            ->when($request->filled('status'), fn ($q) => $q->byStatus((string) $request->input('status')))
            ->when($request->boolean('overdue'), fn ($q) => $q->overdue())
            ->latest('borrowed_at')
            ->paginate(10)
            ->withQueryString();

        return view('circulation.loans.index', compact('loans'));
    }

    /**
     * Form peminjaman. Data anggota & buku diambil lewat contract,
     * bukan langsung dari model context lain.
     */
    public function create()
    {
        $members = $this->memberService->getEligibleMembers();
        $books = $this->bookService->getAvailableBooks();

        return view('circulation.loans.create', compact('members', 'books'));
    }

    public function store(StoreLoanRequest $request)
    {
        $loan = $this->loanService->borrow(
            $request->integer('member_id'),
            $request->input('items', []),
            $request->input('notes'),
            auth()->id(),
        );

        return redirect()
            ->route('loans.show', $loan)
            ->with('success', "Peminjaman {$loan->loan_number} berhasil dibuat.");
    }

    public function show(Loan $loan)
    {
        $loan->load([
            'items',
            'fines',
            'statusHistories.changedBy',
            'member',
        ]);

        return view('circulation.loans.show', compact('loan'));
    }

    public function returnBook(Request $request, Loan $loan)
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $returned = $this->loanService->returnBook(
            $loan->id,
            $data['notes'] ?? null,
            auth()->id(),
        );

        $message = "Peminjaman {$returned->loan_number} berhasil dikembalikan.";

        if ((float) $returned->total_fine > 0) {
            $message .= ' Denda: Rp ' . number_format((float) $returned->total_fine, 0, ',', '.');
        }

        return redirect()
            ->route('loans.show', $returned)
            ->with('success', $message);
    }

    public function destroy(Loan $loan)
    {
        $loan->delete();

        return redirect()
            ->route('loans.index')
            ->with('success', "Peminjaman {$loan->loan_number} berhasil dihapus (soft delete).");
    }

    public function restore(int $id)
    {
        $loan = Loan::withTrashed()->findOrFail($id);
        $loan->restore();

        return redirect()
            ->route('loans.index')
            ->with('success', "Peminjaman {$loan->loan_number} berhasil dipulihkan.");
    }
}
