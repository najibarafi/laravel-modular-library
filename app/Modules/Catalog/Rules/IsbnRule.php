<?php

namespace Modules\Catalog\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Modules\Catalog\ValueObjects\ISBN;

class IsbnRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            new ISBN((string) $value);
        } catch (\Throwable $e) {
            $fail($e->getMessage());
        }
    }
}
