<?php

namespace Modules\Shared\Exceptions;

use Exception;

class LoanAlreadyReturnedException extends Exception
{
    public function __construct(string $loanNumber)
    {
        parent::__construct("Peminjaman {$loanNumber} sudah dikembalikan sebelumnya.");
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'loan_already_returned',
            ], 422);
        }

        return back()->withErrors([
            'loan' => $this->getMessage(),
        ]);
    }
}
