<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Course;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\FeeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\TeacherAttendanceController;
use App\Http\Controllers\Api\StudentAttendanceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AdminRequestController;
use App\Http\Controllers\Api\GradeReportController;

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

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/locations', [LocationController::class, 'getLocations']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Location checking endpoint
    Route::post('/check-location', [LocationController::class, 'checkLocation']);
    
    // Message routes (available to all authenticated users)
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/sent', [MessageController::class, 'sent']);
    Route::get('/messages/received', [MessageController::class, 'received']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
    Route::get('/messages/{message}', [MessageController::class, 'show']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead']);
    
    // Notification routes (available to all authenticated users)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/notifications/{notification}', [NotificationController::class, 'show']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications', [NotificationController::class, 'deleteAll']);
    
    // Routes grouped by roles
    Route::middleware(['role:Admin'])->prefix('admin')->group(function () {
        // User management for Admin
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::get('/roles', [UserController::class, 'getRoles']);
        Route::get('/user-groups', [UserController::class, 'getGroups']);
        Route::get('/students', [UserController::class, 'getStudents']);
        Route::get('/teachers', [UserController::class, 'getTeachers']);
        
        // Course management for Admin
        Route::get('/courses', [CourseController::class, 'index']);
        Route::post('/courses', [CourseController::class, 'store']);
        Route::get('/courses/{course}', [CourseController::class, 'show']);
        Route::put('/courses/{course}', [CourseController::class, 'update']);
        Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
        Route::get('/course-teachers', [CourseController::class, 'getTeachers']);
        Route::get('/course-groups', [CourseController::class, 'getGroups']);
        
        // Fee management for Admin
        Route::get('/fees', [FeeController::class, 'index']);
        Route::post('/fees', [FeeController::class, 'store']);
        Route::get('/fees/{id}', [FeeController::class, 'show']);
        Route::put('/fees/{id}', [FeeController::class, 'update']);
        Route::delete('/fees/{id}', [FeeController::class, 'destroy']);
        Route::get('/fee-students', [FeeController::class, 'getStudents']);
        
        // Group management for Admin
        Route::get('/groups', [GroupController::class, 'index']);
        Route::post('/groups', [GroupController::class, 'store']);
        Route::get('/groups/{group}', [GroupController::class, 'show']);
        Route::put('/groups/{group}', [GroupController::class, 'update']);
        Route::delete('/groups/{group}', [GroupController::class, 'destroy']);
        Route::get('/groups/{group}/students', [GroupController::class, 'getStudents']);
        Route::get('/groups/{group}/courses', [GroupController::class, 'getCourses']);
        
        // Grade management for Admin
        Route::get('/grades', [GradeController::class, 'index']);
        Route::get('/grades/{grade}', [GradeController::class, 'show']);
        Route::get('/courses/{course}/report', [GradeController::class, 'courseReport']);
        Route::get('/groups/{group}/report', [GradeController::class, 'groupReport']);
        
        // Exam management for Admin
        Route::get('/exams', [ExamController::class, 'index']);
        Route::get('/exams/{exam}', [ExamController::class, 'show']);
        Route::get('/exams/{exam}/results', [ExamController::class, 'examResults']);
        
        // Schedule management for Admin
        Route::get('/schedules', [ScheduleController::class, 'index']);
        Route::post('/schedules', [ScheduleController::class, 'store']);
        Route::get('/schedules/{schedule}', [ScheduleController::class, 'show']);
        Route::put('/schedules/{schedule}', [ScheduleController::class, 'update']);
        Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy']);
        Route::get('/groups/{group}/schedules', [ScheduleController::class, 'groupSchedules']);
        Route::get('/courses/{course}/schedules', [ScheduleController::class, 'courseSchedules']);
        
        // Teacher attendance management for Admin
        Route::get('/teacher-attendance', [TeacherAttendanceController::class, 'index']);
        Route::get('/teacher-attendance/{attendance}', [TeacherAttendanceController::class, 'show']);
        Route::delete('/teacher-attendance/{attendance}', [TeacherAttendanceController::class, 'destroy']);
        Route::get('/teachers/{teacher}/attendance/stats', [TeacherAttendanceController::class, 'teacherStats']);
        Route::get('/courses/{course}/teacher-attendance/stats', [TeacherAttendanceController::class, 'courseStats']);
        
        // Student attendance management for Admin
        Route::get('/student-attendance', [StudentAttendanceController::class, 'index']);
        Route::get('/student-attendance/{attendance}', [StudentAttendanceController::class, 'show']);
        Route::delete('/student-attendance/{attendance}', [StudentAttendanceController::class, 'destroy']);
        Route::get('/students/{student}/attendance/stats', [StudentAttendanceController::class, 'studentStats']);
        Route::get('/courses/{course}/student-attendance/stats', [StudentAttendanceController::class, 'courseStats']);
        Route::get('/groups/{group}/student-attendance/stats', [StudentAttendanceController::class, 'groupStats']);
        
        // Notification management for Admin
        Route::post('/notifications', [NotificationController::class, 'store']);
    });
    
    Route::middleware(['role:Teacher'])->prefix('teacher')->group(function () {
        // Course management for Teacher
        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/courses/{course}', [CourseController::class, 'show']);
        
        // Grade management for Teacher
        Route::get('/grades', [GradeController::class, 'index']);
        Route::post('/grades', [GradeController::class, 'store']);
        Route::get('/grades/{grade}', [GradeController::class, 'show']);
        Route::put('/grades/{grade}', [GradeController::class, 'update']);
        Route::get('/courses/{course}/report', [GradeController::class, 'courseReport']);
        
        // Exam management for Teacher
        Route::get('/exams', [ExamController::class, 'index']);
        Route::post('/exams', [ExamController::class, 'store']);
        Route::get('/exams/{exam}', [ExamController::class, 'show']);
        Route::put('/exams/{exam}', [ExamController::class, 'update']);
        Route::delete('/exams/{exam}', [ExamController::class, 'destroy']);
        Route::get('/exams/{exam}/results', [ExamController::class, 'examResults']);
        
        // Schedule access for Teacher
        Route::get('/schedules', [ScheduleController::class, 'index']);
        Route::get('/schedules/{schedule}', [ScheduleController::class, 'show']);
        Route::get('/groups/{group}/schedules', [ScheduleController::class, 'groupSchedules']);
        Route::get('/courses/{course}/schedules', [ScheduleController::class, 'courseSchedules']);
        
        // Teacher attendance management for Teacher
        Route::get('/attendance', [TeacherAttendanceController::class, 'index']);
        Route::post('/attendance', [TeacherAttendanceController::class, 'store']);
        Route::get('/attendance/{attendance}', [TeacherAttendanceController::class, 'show']);
        Route::put('/attendance/{attendance}', [TeacherAttendanceController::class, 'update']);
        Route::get('/attendance/stats', [TeacherAttendanceController::class, 'teacherStats']);
        
        // Student attendance management for Teacher
        Route::get('/student-attendance', [StudentAttendanceController::class, 'index']);
        Route::post('/student-attendance', [StudentAttendanceController::class, 'store']);
        Route::post('/student-attendance/bulk', [StudentAttendanceController::class, 'storeBulk']);
        Route::get('/student-attendance/{attendance}', [StudentAttendanceController::class, 'show']);
        Route::put('/student-attendance/{attendance}', [StudentAttendanceController::class, 'update']);
        Route::get('/students/{student}/attendance/stats', [StudentAttendanceController::class, 'studentStats']);
        Route::get('/courses/{course}/student-attendance/stats', [StudentAttendanceController::class, 'courseStats']);
        Route::get('/groups/{group}/student-attendance/stats', [StudentAttendanceController::class, 'groupStats']);
    });
    
    Route::middleware(['role:Student'])->prefix('student')->group(function () {
        // Course access for Student
        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/courses/{course}', [CourseController::class, 'show']);
        
        // Fee management for Student
        Route::get('/fees', [FeeController::class, 'studentFees']);
        Route::get('/fees/statement', [FeeController::class, 'statement']);
        Route::get('/fees/payments', [FeeController::class, 'paymentHistory']);
        Route::get('/fees/{id}', [FeeController::class, 'show']);
        Route::post('/fees/{id}/payment', [FeeController::class, 'createPaymentTransaction']);
        Route::post('/fees/payment/{transactionId}/process', [FeeController::class, 'processPayment']);
        Route::get('/fees/payment/receipt/{paymentId}', [FeeController::class, 'getReceipt']);
        
        // Grade access for Student
        Route::get('/grades', [GradeController::class, 'index']);
        Route::get('/grades/{grade}', [GradeController::class, 'show']);
        
        // Exam access for Student
        Route::get('/exams', [ExamController::class, 'index']);
        Route::get('/exams/{exam}', [ExamController::class, 'show']);
        Route::post('/exams/{exam}/submit', [ExamController::class, 'submitAttempt']);
        Route::get('/exams/{exam}/results', [ExamController::class, 'studentResults']);
        
        // Schedule access for Student
        Route::get('/schedules', [ScheduleController::class, 'index']);
        Route::get('/schedules/{schedule}', [ScheduleController::class, 'show']);
        Route::get('/groups/{group}/schedules', [ScheduleController::class, 'groupSchedules']);
        
        // Student attendance access for Student
        Route::get('/attendance', [StudentAttendanceController::class, 'index']);
        Route::get('/attendance/{attendance}', [StudentAttendanceController::class, 'show']);
        Route::get('/attendance/stats', [StudentAttendanceController::class, 'studentStats']);
    });

    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Admin request routes
    Route::get('/admin-requests', [AdminRequestController::class, 'index']);
    Route::post('/admin-requests', [AdminRequestController::class, 'store']);
    Route::get('/admin-requests/{adminRequest}', [AdminRequestController::class, 'show']);
    Route::post('/admin-requests/{adminRequest}/status', [AdminRequestController::class, 'updateStatus']);
    Route::post('/admin-requests/{adminRequest}/responses', [AdminRequestController::class, 'addResponse']);

    // Grade report routes
    Route::get('/reports/students/{student}', [GradeReportController::class, 'studentReport']);
    Route::get('/reports/courses/{course}', [GradeReportController::class, 'courseReport']);
    Route::get('/reports/groups/{group}', [GradeReportController::class, 'groupReport']);
    Route::get('/reports/semester', [GradeReportController::class, 'semesterReport']);
});
