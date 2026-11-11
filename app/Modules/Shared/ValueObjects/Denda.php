<?php

namespace Modules\Shared\ValueObjects;

use InvalidArgumentException;

class Denda
{
    private float $amount;
    private const TARIF_PER_HARI = 2000; // Misal denda Rp2.000 per hari keterlambatan

    public function __construct(int $hariTerlambat)
    {
        if ($hariTerlambat < 0) {
            throw new InvalidArgumentException("Hari keterlambatan tidak boleh negatif.");
        }
        
        $this->amount = $hariTerlambat * self::TARIF_PER_HARI;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function formatRupiah(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}