<?php

use Illuminate\Support\Facades\Route;
use Modules\Circulation\Controllers\FineController;
use Modules\Circulation\Controllers\LoanController;

/*
|--------------------------------------------------------------------------
| Circulation Context Routes
|--------------------------------------------------------------------------
| Route admin didaftarkan lebih dulu agar /loans/create tidak tertangkap
| oleh /loans/{loan}.
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::middleware('role:admin')->group(function () {
        Route::resource('loans', LoanController::class)->only(['create', 'store', 'destroy']);

        Route::post('loans/{loan}/return', [LoanController::class, 'returnBook'])->name('loans.return');
        Route::post('loans/{id}/restore', [LoanController::class, 'restore'])->name('loans.restore');

        Route::post('fines/{fine}/pay', [FineController::class, 'pay'])->name('fines.pay');
    });

    Route::resource('loans', LoanController::class)->only(['index', 'show']);
    Route::resource('fines', FineController::class)->only(['index']);
});
