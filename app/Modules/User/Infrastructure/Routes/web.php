<?php

use Illuminate\Support\Facades\Route;
use App\Modules\User\Infrastructure\Controllers\Web\UserController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('users', UserController::class);
    Route::post('users/{user}/roles/{role}', [UserController::class, 'assignRole'])->name('users.roles.assign');
    Route::delete('users/{user}/roles/{role}', [UserController::class, 'removeRole'])->name('users.roles.remove');
}); 