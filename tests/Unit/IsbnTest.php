<?php

use Modules\Catalog\ValueObjects\ISBN;

test('ISBN-13 valid diterima dan dinormalisasi', function () {
    $isbn = new ISBN('978-0-306-40615-7');

    expect($isbn->getValue())->toBe('9780306406157')
        ->and($isbn->getFormatted())->toBe('978-0-3064-0615-7')
        ->and((string) $isbn)->toBe('9780306406157');
});

test('ISBN-10 valid diterima', function () {
    $isbn = new ISBN('0-306-40615-2');

    expect($isbn->getValue())->toBe('0306406152');
});

test('ISBN-10 dengan check digit X valid', function () {
    $isbn = new ISBN('0-8044-2957-X');

    expect($isbn->getValue())->toBe('080442957X');
});

test('ISBN dengan checksum salah ditolak', function () {
    new ISBN('9780306406158');
})->throws(InvalidArgumentException::class);

test('ISBN-13 dengan prefix selain 978/979 ditolak', function () {
    new ISBN('1234567890128');
})->throws(InvalidArgumentException::class);

test('ISBN dengan format karakter salah ditolak', function () {
    new ISBN('978-ABC-123');
})->throws(InvalidArgumentException::class);

test('helper isValid mengembalikan boolean', function () {
    expect(ISBN::isValid('9780306406157'))->toBeTrue()
        ->and(ISBN::isValid('9780306406158'))->toBeFalse();
});
