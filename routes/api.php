<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::get('/locations', [App\Http\Controllers\Api\LocationController::class, 'getLocations']);

Route::middleware('auth:sanctum')->group(function () {
    // Location checking endpoint
    Route::post('/check-location', [App\Http\Controllers\Api\LocationController::class, 'checkLocation']);
});
