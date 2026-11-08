<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')
                ->nullable()
                ->constrained('authors')
                ->nullOnDelete();
            $table->foreignId('publisher_id')
                ->nullable()
                ->constrained('publishers')
                ->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('isbn', 20)->unique();
            $table->text('description')->nullable();
            $table->integer('published_year')->nullable();
            $table->string('language', 50)->default('Indonesia');
            $table->integer('stock')->default(0);
            $table->integer('low_stock_threshold')->default(2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
