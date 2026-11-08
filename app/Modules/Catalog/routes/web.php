<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Controllers\AuthorController;
use Modules\Catalog\Controllers\BookController;
use Modules\Catalog\Controllers\CategoryController;
use Modules\Catalog\Controllers\PublisherController;

/*
|--------------------------------------------------------------------------
| Catalog Context Routes
|--------------------------------------------------------------------------
| Operasi tulis (create/update/delete/restore) hanya untuk admin.
| Route admin didaftarkan lebih dulu agar /books/create tidak tertangkap
| oleh /books/{book}.
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::middleware('role:admin')->group(function () {
        Route::resource('books', BookController::class)->except(['index', 'show']);
        Route::resource('categories', CategoryController::class)->except(['index', 'show']);
        Route::resource('authors', AuthorController::class)->except(['index', 'show']);
        Route::resource('publishers', PublisherController::class)->except(['index', 'show']);

        Route::post('books/{id}/restore', [BookController::class, 'restore'])->name('books.restore');
        Route::post('categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
    });

    Route::resource('books', BookController::class)->only(['index', 'show']);
    Route::resource('categories', CategoryController::class)->only(['index']);
    Route::resource('authors', AuthorController::class)->only(['index']);
    Route::resource('publishers', PublisherController::class)->only(['index']);
});
