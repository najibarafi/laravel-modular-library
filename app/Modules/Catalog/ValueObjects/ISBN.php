<?php

namespace Modules\Catalog\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object ISBN (International Standard Book Number).
 *
 * Mendukung ISBN-10 dan ISBN-13, memvalidasi checksum, serta
 * menormalisasi input (menghapus spasi dan tanda hubung).
 */
final class ISBN
{
    private string $value;

    private string $formatted;

    public function __construct(string $value)
    {
        $normalized = strtoupper(preg_replace('/[\s\-]/', '', trim($value)) ?? '');

        if (!preg_match('/^(?:\d{9}[\dX]|\d{13})$/', $normalized)) {
            throw new InvalidArgumentException(
                "Format ISBN tidak valid: '{$value}'. Gunakan ISBN-10 (10 karakter) atau ISBN-13 (13 digit)."
            );
        }

        if (strlen($normalized) === 10) {
            if (! self::isValidIsbn10($normalized)) {
                throw new InvalidArgumentException("Checksum ISBN-10 tidak valid: '{$value}'.");
            }
        } else {
            if (! str_starts_with($normalized, '978') && ! str_starts_with($normalized, '979')) {
                throw new InvalidArgumentException(
                    "ISBN-13 harus diawali prefix 978 atau 979: '{$value}'."
                );
            }

            if (! self::isValidIsbn13($normalized)) {
                throw new InvalidArgumentException("Checksum ISBN-13 tidak valid: '{$value}'.");
            }
        }

        $this->value = $normalized;
        $this->formatted = self::format($normalized);
    }

    public static function isValid(string $value): bool
    {
        try {
            new self($value);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFormatted(): string
    {
        return $this->formatted;
    }

    public function equals(ISBN $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function isValidIsbn10(string $isbn): bool
    {
        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $digit = $isbn[$i] === 'X' ? 10 : (int) $isbn[$i];
            $sum += $digit * (10 - $i);
        }

        return $sum % 11 === 0;
    }

    private static function isValidIsbn13(string $isbn): bool
    {
        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $isbn[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $checkDigit === (int) $isbn[12];
    }

    private static function format(string $isbn): string
    {
        if (strlen($isbn) === 13) {
            return substr($isbn, 0, 3) . '-' .
                substr($isbn, 3, 1) . '-' .
                substr($isbn, 4, 4) . '-' .
                substr($isbn, 8, 4) . '-' .
                substr($isbn, 12, 1);
        }

        return substr($isbn, 0, 1) . '-' .
            substr($isbn, 1, 3) . '-' .
            substr($isbn, 4, 4) . '-' .
            substr($isbn, 8, 2);
    }
}
