<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Group;
use App\Models\Grade;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\Fee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics based on user role.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            return $this->adminDashboard();
        } elseif ($user->hasRole('Teacher')) {
            return $this->teacherDashboard();
        } elseif ($user->hasRole('Student')) {
            return $this->studentDashboard();
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized access'
        ], 403);
    }

    /**
     * Get admin dashboard statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function adminDashboard()
    {
        // Get counts
        $totalStudents = User::whereHas('roles', function($q) {
            $q->where('name', 'Student');
        })->count();

        $totalTeachers = User::whereHas('roles', function($q) {
            $q->where('name', 'Teacher');
        })->count();

        $totalCourses = Course::count();
        $totalGroups = Group::where('active', true)->count();

        // Get recent activities
        $recentGrades = Grade::with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentAttendance = StudentAttendance::with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get financial statistics
        $totalFees = Fee::sum('total_amount');
        $paidFees = Fee::where('status', 'paid')->sum('total_amount');
        $unpaidFees = Fee::where('status', 'unpaid')->sum('total_amount');

        // Get attendance statistics
        $todayStudentAttendance = StudentAttendance::whereDate('attendance_date', Carbon::today())
            ->count();
        $todayTeacherAttendance = TeacherAttendance::whereDate('attendance_date', Carbon::today())
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'counts' => [
                    'students' => $totalStudents,
                    'teachers' => $totalTeachers,
                    'courses' => $totalCourses,
                    'groups' => $totalGroups
                ],
                'financial' => [
                    'total_fees' => $totalFees,
                    'paid_fees' => $paidFees,
                    'unpaid_fees' => $unpaidFees,
                    'collection_rate' => $totalFees > 0 ? round(($paidFees / $totalFees) * 100, 2) : 0
                ],
                'attendance' => [
                    'today_student_attendance' => $todayStudentAttendance,
                    'today_teacher_attendance' => $todayTeacherAttendance
                ],
                'recent_activities' => [
                    'grades' => $recentGrades,
                    'attendance' => $recentAttendance
                ]
            ]
        ]);
    }

    /**
     * Get teacher dashboard statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function teacherDashboard()
    {
        $teacher = Auth::user();
        
        // Get teacher's courses
        $courses = $teacher->teacherCourses()
            ->with(['groups'])
            ->get();

        // Get total students in teacher's courses
        $totalStudents = 0;
        foreach ($courses as $course) {
            foreach ($course->groups as $group) {
                $totalStudents += $group->students()->count();
            }
        }

        // Get recent grades given by teacher
        $recentGrades = Grade::whereIn('course_id', $courses->pluck('id'))
            ->with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get recent attendance records
        $recentAttendance = StudentAttendance::whereIn('course_id', $courses->pluck('id'))
            ->with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get teacher's attendance statistics
        $teacherAttendance = TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereMonth('attendance_date', Carbon::now()->month)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'counts' => [
                    'courses' => $courses->count(),
                    'students' => $totalStudents,
                    'groups' => $courses->pluck('groups')->flatten()->unique('id')->count()
                ],
                'courses' => $courses,
                'attendance' => [
                    'this_month' => $teacherAttendance->count(),
                    'attendance_rate' => $this->calculateAttendanceRate($teacherAttendance)
                ],
                'recent_activities' => [
                    'grades' => $recentGrades,
                    'attendance' => $recentAttendance
                ]
            ]
        ]);
    }

    /**
     * Get student dashboard statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function studentDashboard()
    {
        $student = Auth::user();
        
        // Get student's group and courses
        $group = $student->group;
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student is not assigned to any group'
            ], 404);
        }

        $courses = $group->courses;

        // Get student's grades
        $grades = Grade::where('student_id', $student->id)
            ->with('course')
            ->get();

        // Calculate GPA
        $totalGrades = 0;
        $validGrades = 0;
        foreach ($grades as $grade) {
            if ($grade->total > 0) {
                $totalGrades += $grade->total;
                $validGrades++;
            }
        }
        $gpa = $validGrades > 0 ? round($totalGrades / $validGrades, 2) : 0;

        // Get attendance statistics
        $attendance = StudentAttendance::where('student_id', $student->id)
            ->whereMonth('attendance_date', Carbon::now()->month)
            ->get();

        // Get upcoming exams
        $upcomingExams = $group->exams()
            ->where('start_time', '>', Carbon::now())
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // Get fees information
        $fees = Fee::where('user_id', $student->id)->get();
        $totalFees = $fees->sum('total_amount');
        $paidFees = $fees->where('status', 'paid')->sum('total_amount');

        return response()->json([
            'status' => 'success',
            'data' => [
                'academic' => [
                    'group' => $group,
                    'courses' => $courses,
                    'gpa' => $gpa
                ],
                'attendance' => [
                    'this_month' => $attendance->count(),
                    'attendance_rate' => $this->calculateAttendanceRate($attendance)
                ],
                'financial' => [
                    'total_fees' => $totalFees,
                    'paid_fees' => $paidFees,
                    'remaining_fees' => $totalFees - $paidFees,
                    'payment_rate' => $totalFees > 0 ? round(($paidFees / $totalFees) * 100, 2) : 0
                ],
                'upcoming_exams' => $upcomingExams,
                'recent_grades' => $grades->sortByDesc('created_at')->take(5)
            ]
        ]);
    }

    /**
     * Calculate attendance rate.
     *
     * @param  \Illuminate\Support\Collection  $attendance
     * @return float
     */
    private function calculateAttendanceRate($attendance)
    {
        if ($attendance->isEmpty()) {
            return 0;
        }

        $present = $attendance->whereIn('status', ['present', 'late'])->count();
        return round(($present / $attendance->count()) * 100, 2);
    }
} 