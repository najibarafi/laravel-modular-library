<?php

namespace Modules\Shared\ValueObjects;

use InvalidArgumentException;

class ISBN
{
    private string $value;

    public function __construct(string $value)
    {
        // Menghapus tanda strip jika ada
        $cleaned = str_replace('-', '', $value);
        
        // Validasi sederhana: ISBN biasanya 10 atau 13 digit
        if (strlen($cleaned) !== 10 && strlen($cleaned) !== 13) {
            throw new InvalidArgumentException("Format ISBN tidak valid: {$value}");
        }
        
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}