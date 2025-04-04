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
        
        // Group management routes
        Route::resource('groups', App\Http\Controllers\GroupController::class);
        
        // مسار مباشر لإدارة المقررات للمسؤول
        Route::get('/admin/courses', [App\Http\Controllers\CourseController::class, 'adminCourses'])->name('admin.courses');
        Route::get('/admin/courses/create', [App\Http\Controllers\CourseController::class, 'create'])->name('admin.courses.create');
        Route::post('/admin/courses', [App\Http\Controllers\CourseController::class, 'store'])->name('admin.courses.store');
        Route::get('/admin/courses/{course}', [App\Http\Controllers\CourseController::class, 'adminShow'])->name('admin.courses.show');
        Route::get('/admin/courses/{course}/edit', [App\Http\Controllers\CourseController::class, 'edit'])->name('admin.courses.edit');
        Route::put('/admin/courses/{course}', [App\Http\Controllers\CourseController::class, 'update'])->name('admin.courses.update');
        Route::delete('/admin/courses/{course}', [App\Http\Controllers\CourseController::class, 'destroy'])->name('admin.courses.destroy');
        
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
        // روابط عرض المقررات للطلاب
        Route::get('/student/courses', [App\Http\Controllers\CourseController::class, 'studentCourses'])->name('courses.student');
    });
    
    // Course routes - accessible by admin and teachers
    Route::middleware(['role:Admin|Teacher'])->group(function () {
        Route::resource('courses', App\Http\Controllers\CourseController::class);
    });
    
    // روابط عرض المقررات للمدرسين - متاحة للمدرسين فقط
    Route::middleware(['role:Teacher'])->group(function () {
        Route::get('/teacher/courses', [App\Http\Controllers\CourseController::class, 'teacherCourses'])->name('courses.teacher');
    });
});

// Route for testing purposes - temporary
Route::get('/generate-test-data', function () {
    // Create test groups if they don't exist
    if (\App\Models\Group::count() == 0) {
        $groups = [
            ['name' => 'مجموعة هندسة البرمجيات', 'description' => 'مجموعة متخصصة في هندسة البرمجيات والتطوير', 'active' => true],
            ['name' => 'مجموعة تطوير الويب', 'description' => 'مجموعة متخصصة في تطوير تطبيقات الويب', 'active' => true],
            ['name' => 'مجموعة الذكاء الاصطناعي', 'description' => 'مجموعة متخصصة في الذكاء الاصطناعي وتعلم الآلة', 'active' => true],
            ['name' => 'مجموعة تحليل البيانات', 'description' => 'مجموعة متخصصة في علم البيانات وتحليلها', 'active' => false]
        ];

        foreach ($groups as $groupData) {
            \App\Models\Group::create($groupData);
        }
    }

    return redirect()->route('groups.index')->with('success', 'تم إنشاء بيانات اختبارية للمجموعات');
});

// Route for testing purposes - temporary
Route::get('/generate-admin-user', function () {
    // Check if admin user already exists
    $adminEmail = 'admin@otu.edu';
    $existingAdmin = \App\Models\User::where('email', $adminEmail)->first();
    
    if (!$existingAdmin) {
        // Get admin role
        $adminRole = \App\Models\Role::where('name', 'Admin')->first();
        
        if (!$adminRole) {
            return redirect()->route('dashboard')->with('error', 'دور المدير غير موجود في النظام');
        }
        
        // Create admin user
        $admin = \App\Models\User::create([
            'name' => 'مدير النظام',
            'email' => $adminEmail,
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);
        
        // Assign admin role
        $admin->roles()->attach($adminRole);
        
        return redirect()->route('login')->with('success', 'تم إنشاء حساب المدير بنجاح. البريد الإلكتروني: ' . $adminEmail . ' وكلمة المرور: password123');
    }
    
    return redirect()->route('login')->with('info', 'حساب المدير موجود بالفعل. البريد الإلكتروني: ' . $adminEmail);
});
