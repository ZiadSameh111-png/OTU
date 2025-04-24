<?php

use Illuminate\Support\Facades\Route;
use App\Modules\User\Infrastructure\Controllers\Api\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/roles/{role}', [UserController::class, 'assignRole']);
    Route::delete('users/{user}/roles/{role}', [UserController::class, 'removeRole']);
}); 