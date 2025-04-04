<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TeacherAttendanceController;
use App\Http\Controllers\GradeController;

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
        
        // Admin Request management routes
        Route::get('/admin/requests', [App\Http\Controllers\AdminRequestController::class, 'index'])->name('admin.requests');
        Route::get('/admin/requests/{request}', [App\Http\Controllers\AdminRequestController::class, 'show'])->name('admin.requests.show');
        Route::put('/admin/requests/{request}', [App\Http\Controllers\AdminRequestController::class, 'update'])->name('admin.requests.update');
        
        // Teacher attendance management routes
        Route::get('/admin/attendance', [App\Http\Controllers\TeacherAttendanceController::class, 'index'])->name('admin.attendance');
        Route::get('/admin/attendance/create', [App\Http\Controllers\TeacherAttendanceController::class, 'create'])->name('admin.attendance.create');
        Route::post('/admin/attendance', [App\Http\Controllers\TeacherAttendanceController::class, 'store'])->name('admin.attendance.store');
        Route::get('/admin/attendance/{attendance}/edit', [App\Http\Controllers\TeacherAttendanceController::class, 'edit'])->name('admin.attendance.edit');
        Route::put('/admin/attendance/{attendance}', [App\Http\Controllers\TeacherAttendanceController::class, 'update'])->name('admin.attendance.update');
        
        // Internal messaging routes for admin
        Route::get('/admin/messages', [App\Http\Controllers\MessageController::class, 'adminIndex'])->name('admin.messages');
        Route::get('/admin/messages/create', [App\Http\Controllers\MessageController::class, 'adminCreate'])->name('admin.messages.create');
        Route::post('/admin/messages', [App\Http\Controllers\MessageController::class, 'adminStore'])->name('admin.messages.store');
        Route::get('/admin/messages/{message}', [App\Http\Controllers\MessageController::class, 'adminShow'])->name('admin.messages.show');
        Route::post('/admin/messages/toggle-star/{message}', [App\Http\Controllers\MessageController::class, 'toggleStar'])->name('admin.messages.toggle-star');
        Route::post('/admin/messages/mark-read', [App\Http\Controllers\MessageController::class, 'markAsRead'])->name('admin.messages.mark-read');
        Route::post('/admin/messages/mark-star', [App\Http\Controllers\MessageController::class, 'markAsStar'])->name('admin.messages.mark-star');
        Route::post('/admin/messages/batch-delete', [App\Http\Controllers\MessageController::class, 'batchDelete'])->name('admin.messages.batch-delete');
        Route::delete('/admin/messages/{message}', [App\Http\Controllers\MessageController::class, 'destroy'])->name('admin.messages.destroy');
        
        // Fee management routes
        Route::get('/admin/fees', [App\Http\Controllers\FeeController::class, 'index'])->name('admin.fees');
        Route::get('/admin/fees/create', [App\Http\Controllers\FeeController::class, 'create'])->name('admin.fees.create');
        Route::post('/admin/fees', [App\Http\Controllers\FeeController::class, 'store'])->name('admin.fees.store');
        Route::get('/admin/fees/{fee}/edit', [App\Http\Controllers\FeeController::class, 'edit'])->name('admin.fees.edit');
        Route::put('/admin/fees/{fee}', [App\Http\Controllers\FeeController::class, 'update'])->name('admin.fees.update');
        Route::get('/admin/fees/{fee}/payments', [App\Http\Controllers\FeePaymentController::class, 'index'])->name('admin.fees.payments');
        Route::get('/admin/fees/{fee}/payments/create', [App\Http\Controllers\FeePaymentController::class, 'create'])->name('admin.fees.payments.create');
        Route::post('/admin/fees/{fee}/payments', [App\Http\Controllers\FeePaymentController::class, 'store'])->name('admin.fees.payments.store');
        
        // روابط الرسوم والمدفوعات للمسؤولين
        Route::resource('fees', App\Http\Controllers\FeesController::class);
        Route::resource('payments', App\Http\Controllers\FeePaymentsController::class);
        
        // روابط خاصة للمدفوعات
        Route::get('fees/{fee}/payment', [App\Http\Controllers\FeesController::class, 'createPayment'])->name('fees.payment.create');
        Route::post('fees/{fee}/payment', [App\Http\Controllers\FeesController::class, 'storePayment'])->name('fees.payment.store');

        // Add after admin routes within the Admin middleware group
        Route::get('/admin/students', [App\Http\Controllers\UserController::class, 'students'])->name('admin.students');
        Route::get('/admin/teachers', [App\Http\Controllers\UserController::class, 'teachers'])->name('admin.teachers');
        Route::get('/admin/groups', [App\Http\Controllers\GroupController::class, 'index'])->name('admin.groups');
        Route::get('/admin/notifications', [App\Http\Controllers\NotificationController::class, 'adminIndex'])->name('admin.notifications');
        
        // Grades Reports Routes
        Route::get('/admin/grades/reports', [App\Http\Controllers\GradeController::class, 'adminReports'])->name('admin.grades.reports');
        Route::get('/admin/grades/course/{course}', [App\Http\Controllers\GradeController::class, 'courseReport'])->name('admin.grades.course.report');
        Route::get('/admin/grades/group/{group}', [App\Http\Controllers\GradeController::class, 'groupReport'])->name('admin.grades.group.report');
        Route::get('/admin/grades/export/course/{course}/{format?}', [App\Http\Controllers\GradeController::class, 'exportCourseGrades'])->name('admin.grades.export.course');
    });
    
    // Schedule view routes - accessible by students
    Route::middleware(['role:Student'])->group(function () {
        Route::get('/student/schedule', [ScheduleController::class, 'studentSchedule'])->name('student.schedule');
        // روابط عرض المقررات للطلاب
        Route::get('/student/courses', [App\Http\Controllers\CourseController::class, 'studentCourses'])->name('courses.student');
        
        // Student administrative request routes
        Route::get('/student/requests', [App\Http\Controllers\AdminRequestController::class, 'studentIndex'])->name('student.requests');
        Route::get('/student/requests/create', [App\Http\Controllers\AdminRequestController::class, 'create'])->name('student.requests.create');
        Route::post('/student/requests', [App\Http\Controllers\AdminRequestController::class, 'store'])->name('student.requests.store');
        Route::get('/student/requests/{request}', [App\Http\Controllers\AdminRequestController::class, 'studentShow'])->name('student.requests.show');
        
        // Student message routes
        Route::get('/student/messages', [App\Http\Controllers\MessageController::class, 'studentIndex'])->name('student.messages');
        Route::get('/student/messages/create', [App\Http\Controllers\MessageController::class, 'studentCreate'])->name('student.messages.create');
        Route::post('/student/messages', [App\Http\Controllers\MessageController::class, 'store'])->name('student.messages.store');
        Route::get('/student/messages/{message}', [App\Http\Controllers\MessageController::class, 'studentShow'])->name('student.messages.show');
        
        // Student fee routes
        Route::get('/student/fees', [App\Http\Controllers\FeesController::class, 'studentFees'])->name('student.fees');
        Route::get('/student/payments', [App\Http\Controllers\FeePaymentsController::class, 'index'])->name('student.payments');
        Route::get('/student/fees/{fee}', [App\Http\Controllers\FeeController::class, 'studentShow'])->name('fees.show');
        Route::get('/student/fees/{fee}/pay', [App\Http\Controllers\FeeController::class, 'pay'])->name('fees.pay');
        Route::post('/student/fees/{fee}/checkout', [App\Http\Controllers\FeeController::class, 'checkout'])->name('fees.checkout');
        Route::get('/student/fees/payment-gateway/{transactionId}', [App\Http\Controllers\FeeController::class, 'paymentGateway'])->name('fees.payment-gateway');
        Route::post('/student/fees/{fee}/process-payment', [App\Http\Controllers\FeeController::class, 'processPayment'])->name('fees.process-payment');
        Route::get('/student/fees/receipt/{paymentId}', [App\Http\Controllers\FeeController::class, 'receipt'])->name('fees.receipt');
        Route::get('/student/fees/statement', [App\Http\Controllers\FeeController::class, 'statement'])->name('fees.statement');
        Route::get('/student/fees/retry/{transactionId}', [App\Http\Controllers\FeeController::class, 'retry'])->name('fees.retry');

        // Add after student schedules routes within the Student middleware group
        Route::get('/student/notifications', [App\Http\Controllers\NotificationController::class, 'studentIndex'])->name('student.notifications');
        Route::get('/student/courses/{course}', [App\Http\Controllers\CourseController::class, 'studentShow'])->name('student.courses.show');
        Route::post('/student/notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('student.notifications.mark-read');
        
        // Grades Routes
        Route::get('/student/grades', [App\Http\Controllers\GradeController::class, 'studentGrades'])->name('student.grades');
        Route::get('/student/grades/course/{course}', [App\Http\Controllers\GradeController::class, 'studentGradeDetails'])->name('student.grades.details');
    });
    
    // Course routes - accessible by admin and teachers
    Route::middleware(['role:Admin|Teacher'])->group(function () {
        Route::resource('courses', App\Http\Controllers\CourseController::class);
    });
    
    // روابط عرض المقررات للمدرسين - متاحة للمدرسين فقط
    Route::middleware(['role:Teacher'])->group(function () {
        Route::get('/teacher/courses', [App\Http\Controllers\CourseController::class, 'teacherCourses'])->name('courses.teacher');
        
        // Teacher message routes
        Route::get('/teacher/messages', [App\Http\Controllers\MessageController::class, 'teacherIndex'])->name('teacher.messages');
        Route::get('/teacher/messages/create', [App\Http\Controllers\MessageController::class, 'teacherCreate'])->name('teacher.messages.create');
        Route::post('/teacher/messages', [App\Http\Controllers\MessageController::class, 'teacherStore'])->name('teacher.messages.store');
        Route::get('/teacher/messages/{message}', [App\Http\Controllers\MessageController::class, 'teacherShow'])->name('teacher.messages.show');
        Route::post('/teacher/messages/{message}/read', [App\Http\Controllers\MessageController::class, 'markMessageRead'])->name('teacher.messages.mark-message-read');
        Route::post('/teacher/messages/toggle-star/{message}', [App\Http\Controllers\MessageController::class, 'toggleStar'])->name('teacher.messages.toggle-star');
        Route::post('/teacher/messages/mark-read', [App\Http\Controllers\MessageController::class, 'markAsRead'])->name('teacher.messages.mark-read');
        Route::post('/teacher/messages/mark-star', [App\Http\Controllers\MessageController::class, 'markAsStar'])->name('teacher.messages.mark-star');
        Route::post('/teacher/messages/batch-delete', [App\Http\Controllers\MessageController::class, 'batchDelete'])->name('teacher.messages.batch-delete');
        Route::delete('/teacher/messages/{message}', [App\Http\Controllers\MessageController::class, 'destroy'])->name('teacher.messages.destroy');

        // Add after teacher courses route within the Teacher middleware group
        Route::get('/teacher/schedule', [ScheduleController::class, 'teacherSchedule'])->name('teacher.schedule');
        Route::get('/teacher/schedule/{schedule}', [ScheduleController::class, 'teacherShow'])->name('teacher.schedule.show');
        Route::get('/teacher/groups', [App\Http\Controllers\GroupController::class, 'teacherGroups'])->name('teacher.groups');
        Route::get('/teacher/notifications', [App\Http\Controllers\NotificationController::class, 'teacherIndex'])->name('teacher.notifications');
        Route::get('/teacher/attendance', [App\Http\Controllers\StudentAttendanceController::class, 'index'])->name('teacher.attendance');
        Route::get('/teacher/attendance/create', [App\Http\Controllers\StudentAttendanceController::class, 'create'])->name('teacher.attendance.create');
        Route::post('/teacher/attendance', [App\Http\Controllers\StudentAttendanceController::class, 'store'])->name('teacher.attendance.store');
        Route::get('/teacher/attendance/{attendance}', [App\Http\Controllers\StudentAttendanceController::class, 'show'])->name('teacher.attendance.show');
        
        // Grades Management Routes
        Route::get('/teacher/grades', [App\Http\Controllers\GradeController::class, 'index'])->name('teacher.grades.index');
        Route::get('/teacher/grades/course/{course}', [App\Http\Controllers\GradeController::class, 'manageCourse'])->name('teacher.grades.manage');
        Route::post('/teacher/grades/course/{course}/update', [App\Http\Controllers\GradeController::class, 'updateBatch'])->name('teacher.grades.update.batch');
        Route::post('/teacher/grades/course/{course}/submit', [App\Http\Controllers\GradeController::class, 'submitFinal'])->name('teacher.grades.submit.final');
    });

    // Grade Management Routes
    Route::middleware(['role:Teacher'])->group(function () {
        Route::get('/teacher/grades', [GradeController::class, 'teacherIndex'])->name('teacher.grades.index');
        Route::get('/teacher/grades/course/{course}', [GradeController::class, 'manageCourse'])->name('teacher.grades.manage');
        Route::post('/teacher/grades/store', [GradeController::class, 'store'])->name('teacher.grades.store');
        Route::post('/teacher/grades/submit', [GradeController::class, 'submit'])->name('teacher.grades.submit');
    });

    // Student Grade Routes
    Route::middleware(['role:Student'])->group(function () {
        Route::get('/student/grades', [GradeController::class, 'studentIndex'])->name('student.grades.index');
    });

    // Admin Grade Routes
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/admin/grades', [GradeController::class, 'adminIndex'])->name('admin.grades.index');
        Route::get('/admin/grades/course/{course}', [GradeController::class, 'adminViewCourse'])->name('admin.grades.view');
    });
});

// Student Admin Requests Routes
Route::middleware(['auth', 'role:Student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/admin-requests', [AdminRequestController::class, 'studentIndex'])->name('admin-requests.index');
    Route::post('/admin-requests', [AdminRequestController::class, 'store'])->name('admin-requests.store');
    Route::delete('/admin-requests/{adminRequest}', [AdminRequestController::class, 'destroy'])->name('admin-requests.destroy');
    Route::get('/admin-requests/download-certificate/{id}', [AdminRequestController::class, 'downloadCertificate'])->name('admin-requests.download-certificate');
    
    // إضافة مسارات إضافية للرسائل
    Route::post('/messages/toggle-star/{message}', [App\Http\Controllers\MessageController::class, 'toggleStar'])->name('messages.toggle-star');
    Route::post('/messages/mark-read', [App\Http\Controllers\MessageController::class, 'markAsRead'])->name('messages.mark-read');
    Route::post('/messages/mark-star', [App\Http\Controllers\MessageController::class, 'markAsStar'])->name('messages.mark-star');
    Route::post('/messages/batch-delete', [App\Http\Controllers\MessageController::class, 'batchDelete'])->name('messages.batch-delete');
    Route::delete('/messages/{message}', [App\Http\Controllers\MessageController::class, 'destroy'])->name('messages.destroy');
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

// Admin Routes
Route::group(['middleware' => ['auth', 'admin'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    // ... existing code ...

    // Grades
    Route::get('/grades/reports', [GradeController::class, 'adminReports'])->name('grades.reports');
    Route::get('/grades/export/{format?}', [GradeController::class, 'export'])->name('grades.export');
    Route::get('/grades/{id}/edit', [GradeController::class, 'edit'])->name('grades.edit');
    Route::put('/grades/{id}', [GradeController::class, 'update'])->name('grades.update');
    Route::get('/courses/{courseId}/report', [GradeController::class, 'courseReport'])->name('courses.report');
    Route::get('/courses/{courseId}/export/{format?}', [GradeController::class, 'exportCourseGrades'])->name('courses.export');

    // ... existing code ...
});

// Student Routes
Route::group(['middleware' => ['auth', 'student'], 'prefix' => 'student', 'as' => 'student.'], function () {
    // ... existing code ...

    // Grades
    Route::get('/grades', [GradeController::class, 'studentGrades'])->name('grades.index');
    Route::get('/grades/{courseId}/details', [GradeController::class, 'studentGradeDetails'])->name('grades.details');

    // ... existing code ...
});
