<?php

namespace Modules\Shared\Exceptions;

use Exception;

class BookNotAvailableException extends Exception
{
    public function __construct(private int $bookId)
    {
        parent::__construct("Buku dengan ID {$bookId} tidak ditemukan atau sudah tidak aktif.");
    }

    public function getBookId(): int
    {
        return $this->bookId;
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'book_not_available',
            ], 422);
        }

        return back()->withInput()->withErrors([
            'book' => $this->getMessage(),
        ]);
    }
}
