<?php

namespace Modules\Circulation\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Circulation\Events\BookReturned;

/**
 * Listener BookReturned: mencatat pengembalian dan denda ke log.
 */
class LogBookReturned
{
    public function handle(BookReturned $event): void
    {
        $loan = $event->loan;
        $denda = $event->denda;

        $message = sprintf(
            'BookReturned | loan=%s | member=%s (#%d) | returned_at=%s | days_late=%d | fine=%s',
            $loan->loan_number,
            $loan->member_name,
            $loan->member_id,
            $loan->returned_at?->toDateTimeString(),
            $denda->getDaysLate(),
            (string) $denda,
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
