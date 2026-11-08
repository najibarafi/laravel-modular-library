<?php

namespace Modules\Circulation\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\ValueObjects\Denda;

class BookReturned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Loan $loan,
        public Denda $denda,
    ) {}
}
