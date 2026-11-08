<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Author;
use Modules\Catalog\Models\Book;
use Modules\Catalog\Models\Publisher;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'author_id' => Author::factory(),
            'publisher_id' => Publisher::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 99999),
            'isbn' => $this->isbn13(),
            'description' => fake()->paragraph(),
            'published_year' => fake()->numberBetween(1990, (int) date('Y')),
            'language' => 'Indonesia',
            'stock' => fake()->numberBetween(0, 40),
            'low_stock_threshold' => 3,
            'is_active' => true,
        ];
    }

    /**
     * Membuat ISBN-13 dengan checksum valid.
     */
    private function isbn13(): string
    {
        $base = '978' . fake()->unique()->numerify('#########');

        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $base[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $base . $checkDigit;
    }
}
