<?php

namespace Modules\Shared\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(
        private string $bookTitle,
        private int $available,
        private int $requested,
    ) {
        parent::__construct(
            "Stok buku '{$bookTitle}' tidak mencukupi. Tersedia: {$available}, diminta: {$requested}."
        );
    }

    public function getBookTitle(): string
    {
        return $this->bookTitle;
    }

    public function getAvailable(): int
    {
        return $this->available;
    }

    public function getRequested(): int
    {
        return $this->requested;
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error' => 'insufficient_stock',
                'details' => [
                    'book' => $this->bookTitle,
                    'available' => $this->available,
                    'requested' => $this->requested,
                ],
            ], 422);
        }

        return back()->withInput()->withErrors([
            'stock' => $this->getMessage(),
        ]);
    }
}
