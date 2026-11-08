<?php

use Illuminate\Support\Facades\Route;
use Modules\Membership\Controllers\MemberController;
use Modules\Membership\Controllers\MembershipController;
use Modules\Membership\Controllers\RoleController;

/*
|--------------------------------------------------------------------------
| Membership Context Routes
|--------------------------------------------------------------------------
| Route admin didaftarkan lebih dulu agar /members/create dan /roles/create
| tidak tertangkap oleh /members/{member} atau /roles/{role}.
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::middleware('role:admin')->group(function () {
        Route::resource('members', MemberController::class)->except(['index', 'show']);
        Route::resource('roles', RoleController::class)->except(['index', 'show']);

        Route::post('members/{id}/restore', [MemberController::class, 'restore'])->name('members.restore');
        Route::post('members/{member}/roles', [MemberController::class, 'updateRoles'])->name('members.roles.update');

        Route::post('members/{member}/memberships', [MembershipController::class, 'store'])->name('members.memberships.store');
        Route::delete('memberships/{membership}', [MembershipController::class, 'destroy'])->name('memberships.destroy');
    });

    Route::resource('members', MemberController::class)->only(['index', 'show']);
    Route::resource('roles', RoleController::class)->only(['index']);
});
