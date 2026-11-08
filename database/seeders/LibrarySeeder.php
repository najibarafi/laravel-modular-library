<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Catalog\Models\Author;
use Modules\Catalog\Models\Book;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Publisher;
use Modules\Circulation\Services\LoanService;
use Modules\Membership\Models\Member;
use Modules\Membership\Models\Role;

class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        if (Book::withTrashed()->exists()) {
            $this->command?->warn('Data perpustakaan sudah ada, LibrarySeeder dilewati.');

            return;
        }

        $roleDefinitions = [
            ['name' => 'Administrator', 'slug' => 'admin', 'description' => 'Pengelola sistem perpustakaan.'],
            ['name' => 'Pustakawan', 'slug' => 'librarian', 'description' => 'Petugas yang melayani peminjaman.'],
            ['name' => 'Anggota', 'slug' => 'member', 'description' => 'Anggota perpustakaan.'],
        ];

        $roles = [];

        foreach ($roleDefinitions as $definition) {
            $roles[$definition['slug']] = Role::firstOrCreate(
                ['slug' => $definition['slug']],
                $definition,
            );
        }

        $authors = Author::factory()->count(8)->create();
        $publishers = Publisher::factory()->count(5)->create();

        $rootCategories = Category::factory()->count(5)->create(['parent_id' => null]);

        foreach ($rootCategories as $root) {
            Category::factory()->count(2)->create(['parent_id' => $root->id]);
        }

        $categories = Category::all();

        $books = collect(range(1, 40))->map(fn () => Book::factory()->create([
            'author_id' => $authors->random()->id,
            'publisher_id' => $publishers->random()->id,
        ]));

        foreach ($books as $book) {
            $sync = [];

            foreach ($categories->random(rand(1, 3)) as $category) {
                $sync[$category->id] = ['added_at' => now()->subDays(rand(1, 60))];
            }

            $book->categories()->sync($sync);
        }

        $members = Member::factory()->count(15)->create();

        foreach ($members as $member) {
            $member->roles()->attach($roles['member']->id, ['assigned_at' => now()]);

            $member->memberships()->create([
                'type' => fake()->randomElement(['basic', 'premium', 'student']),
                'start_date' => now()->subMonths(rand(1, 12)),
                'end_date' => now()->addYear(),
                'fee' => fake()->randomElement([0, 50000, 100000]),
                'status' => 'active',
            ]);
        }

        $loanService = app(LoanService::class);
        $adminId = User::where('role', 'admin')->value('id');

        // 6 peminjaman aktif; peminjaman pertama memuat 2 buku (demo HasMany).
        foreach ($members->take(6) as $index => $member) {
            $available = Book::available()->get();

            if ($available->isEmpty()) {
                break;
            }

            $selected = $index === 0
                ? $available->random(min(2, $available->count()))
                : $available->random(1);

            $items = collect($selected)
                ->map(fn (Book $book) => ['book_id' => $book->id, 'quantity' => 1])
                ->values()
                ->all();

            $loan = $loanService->borrow(
                $member->id,
                $items,
                'Seeder: peminjaman aktif',
                $adminId,
            );

            // Jadikan peminjaman pertama sebagai contoh keterlambatan aktif.
            if ($index === 0) {
                $loan->update(['due_at' => now()->subDays(5)]);
            }
        }

        // 5 peminjaman selesai; 2 di antaranya terlambat sehingga menghasilkan denda.
        foreach ($members->slice(6, 5)->values() as $index => $member) {
            $available = Book::available()->get();

            if ($available->isEmpty()) {
                break;
            }

            $book = $available->random();

            $loan = $loanService->borrow(
                $member->id,
                [['book_id' => $book->id, 'quantity' => 1]],
                'Seeder: peminjaman selesai',
                $adminId,
            );

            if ($index < 2) {
                $loan->update(['due_at' => now()->subDays(3)]);
            }

            $loanService->returnBook($loan->id, 'Seeder: buku dikembalikan', $adminId);
        }

        $this->command?->info('LibrarySeeder selesai: 40 buku, 15 anggota, 11 peminjaman.');
    }
}
