<?php

use Illuminate\Support\Carbon;
use Modules\Circulation\ValueObjects\Denda;

test('denda dihitung otomatis dari jumlah hari terlambat', function () {
    $denda = Denda::fromDaysLate(3);

    expect($denda->getDaysLate())->toBe(3)
        ->and($denda->getAmount())->toBe(3000.0)
        ->and($denda->isLate())->toBeTrue()
        ->and((string) $denda)->toBe('Rp 3.000');
});

test('tidak ada denda jika dikembalikan sebelum jatuh tempo', function () {
    $due = Carbon::parse('2026-11-10 10:00:00');
    $returned = Carbon::parse('2026-11-09 10:00:00');

    $denda = Denda::between($due, $returned);

    expect($denda->getDaysLate())->toBe(0)
        ->and($denda->getAmount())->toBe(0.0)
        ->and($denda->isLate())->toBeFalse();
});

test('keterlambatan sebagian hari dihitung satu hari penuh', function () {
    $due = Carbon::parse('2026-11-10 10:00:00');
    $returned = Carbon::parse('2026-11-10 11:00:00');

    $denda = Denda::between($due, $returned);

    expect($denda->getDaysLate())->toBe(1)
        ->and($denda->getAmount())->toBe(1000.0);
});

test('tarif denda dapat dikustomisasi', function () {
    $denda = Denda::fromDaysLate(2, 2500);

    expect($denda->getAmount())->toBe(5000.0)
        ->and($denda->getRatePerDay())->toBe(2500.0);
});

test('dua value object denda dapat dijumlahkan', function () {
    $total = Denda::fromDaysLate(2)->add(Denda::fromDaysLate(3));

    expect($total->getDaysLate())->toBe(5)
        ->and($total->getAmount())->toBe(5000.0);
});

test('denda negatif ditolak', function () {
    new Denda(-100, 1);
})->throws(InvalidArgumentException::class);

test('hari keterlambatan negatif ditolak', function () {
    new Denda(1000, -1);
})->throws(InvalidArgumentException::class);
