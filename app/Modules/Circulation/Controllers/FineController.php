<?php

namespace Modules\Circulation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Circulation\Models\Fine;
use Modules\Circulation\Services\LoanService;

class FineController extends Controller
{
    public function __construct(
        private LoanService $loanService,
    ) {}

    public function index(Request $request)
    {
        $fines = Fine::with(['loan', 'member'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalUnpaid = Fine::unpaid()->sum('amount');

        return view('circulation.fines.index', compact('fines', 'totalUnpaid'));
    }

    public function pay(Fine $fine)
    {
        $this->loanService->payFine($fine);

        return back()->with('success', 'Denda berhasil ditandai sebagai lunas.');
    }
}
