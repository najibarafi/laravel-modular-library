<?php

namespace Modules\Circulation\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Circulation\Events\BookBorrowed;

/**
 * Listener BookBorrowed: mencatat log/notifikasi peminjaman.
 *
 * Log ditulis ke dua tempat:
 * 1. Laravel log (storage/logs/laravel.log)
 * 2. Log khusus domain event (storage/logs/library-events.log)
 */
class LogBookBorrowed
{
    public function handle(BookBorrowed $event): void
    {
        $loan = $event->loan;

        $message = sprintf(
            'BookBorrowed | loan=%s | member=%s (#%d) | items=%d | borrowed_at=%s | due_at=%s',
            $loan->loan_number,
            $loan->member_name,
            $loan->member_id,
            $loan->items->count(),
            $loan->borrowed_at?->toDateTimeString(),
            $loan->due_at?->toDateTimeString(),
        );

        Log::info($message);
        $this->writeDomainLog($message);
    }

    private function writeDomainLog(string $message): void
    {
        $path = storage_path('logs/library-events.log');

        @file_put_contents(
            $path,
            '[' . now()->toDateTimeString() . '] ' . $message . PHP_EOL,
            FILE_APPEND,
        );
    }
}
