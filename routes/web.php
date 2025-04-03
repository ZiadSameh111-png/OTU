<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScheduleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

// Replace the default home route with the dashboard route
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::redirect('/home', '/dashboard');

// Routes requiring authentication
Route::middleware(['auth'])->group(function () {
    // User management routes - only accessible by admin
    Route::middleware(['role:Admin'])->group(function () {
        Route::resource('users', App\Http\Controllers\UserController::class);
        
        // Schedule management routes - only accessible by admin
        Route::get('/admin/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
        Route::get('/admin/schedules/create', [ScheduleController::class, 'create'])->name('schedules.create');
        Route::post('/admin/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        Route::get('/admin/schedules/{schedule}/edit', [ScheduleController::class, 'edit'])->name('schedules.edit');
        Route::put('/admin/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
        Route::delete('/admin/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
    });
    
    // Schedule view routes - accessible by students
    Route::middleware(['role:Student'])->group(function () {
        Route::get('/student/schedule', [ScheduleController::class, 'studentSchedule'])->name('student.schedule');
    });
    
    // Course routes - accessible by admin and teachers
    Route::middleware(['role:Admin|Teacher'])->group(function () {
        Route::resource('courses', App\Http\Controllers\CourseController::class);
    });
});
