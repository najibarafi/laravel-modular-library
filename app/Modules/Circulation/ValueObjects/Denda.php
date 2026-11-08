<?php

namespace Modules\Circulation\ValueObjects;

use DateTimeInterface;
use InvalidArgumentException;

/**
 * Value Object Denda (fine) dengan perhitungan otomatis.
 *
 * Denda = jumlah hari terlambat x tarif per hari.
 * Keterlambatan dihitung per hari penuh (ceil), sehingga terlambat
 * 1 jam tetap dihitung 1 hari.
 */
final class Denda
{
    public const DEFAULT_RATE_PER_DAY = 1000.0;

    private float $amount;

    private int $daysLate;

    private float $ratePerDay;

    public function __construct(float $amount, int $daysLate, float $ratePerDay = self::DEFAULT_RATE_PER_DAY)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Jumlah denda tidak boleh negatif.');
        }

        if ($daysLate < 0) {
            throw new InvalidArgumentException('Jumlah hari keterlambatan tidak boleh negatif.');
        }

        if ($ratePerDay < 0) {
            throw new InvalidArgumentException('Tarif denda tidak boleh negatif.');
        }

        $this->amount = $amount;
        $this->daysLate = $daysLate;
        $this->ratePerDay = $ratePerDay;
    }

    public static function fromDaysLate(int $daysLate, float $ratePerDay = self::DEFAULT_RATE_PER_DAY): self
    {
        $daysLate = max(0, $daysLate);

        return new self($daysLate * $ratePerDay, $daysLate, $ratePerDay);
    }

    public static function between(
        DateTimeInterface $dueAt,
        DateTimeInterface $returnedAt,
        float $ratePerDay = self::DEFAULT_RATE_PER_DAY,
    ): self {
        $secondsLate = $returnedAt->getTimestamp() - $dueAt->getTimestamp();

        if ($secondsLate <= 0) {
            return self::fromDaysLate(0, $ratePerDay);
        }

        $daysLate = (int) ceil($secondsLate / 86400);

        return self::fromDaysLate($daysLate, $ratePerDay);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getDaysLate(): int
    {
        return $this->daysLate;
    }

    public function getRatePerDay(): float
    {
        return $this->ratePerDay;
    }

    public function isLate(): bool
    {
        return $this->daysLate > 0;
    }

    public function add(Denda $other): self
    {
        if ($this->ratePerDay !== $other->ratePerDay) {
            throw new InvalidArgumentException('Tidak dapat menjumlahkan denda dengan tarif berbeda.');
        }

        return new self(
            $this->amount + $other->amount,
            $this->daysLate + $other->daysLate,
            $this->ratePerDay,
        );
    }

    public function __toString(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}
