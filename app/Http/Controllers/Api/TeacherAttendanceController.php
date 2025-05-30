<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TeacherAttendanceController extends Controller
{
    /**
     * Display a listing of teacher attendance records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin') && !$user->hasRole('Teacher')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $query = TeacherAttendance::with(['teacher', 'course']);

        // If teacher, only show their own attendance
        if ($user->hasRole('Teacher')) {
            $query->where('teacher_id', $user->id);
        }
        // If admin and teacher_id is provided
        elseif ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by course if provided
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('attendance_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('attendance_date', '<=', $request->end_date);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $attendances
        ]);
    }

    /**
     * Store a newly created attendance record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('Teacher')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'attendance_date' => 'required|date|before_or_equal:today',
            'check_in_time' => 'required|date_format:H:i',
            'check_out_time' => 'required|date_format:H:i|after:check_in_time',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if teacher is assigned to this course
        $course = Course::find($request->course_id);
        if (!$course->teachers->contains($user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not assigned to this course'
            ], 403);
        }

        // Check for duplicate attendance record
        $existingAttendance = TeacherAttendance::where('teacher_id', $user->id)
            ->where('course_id', $request->course_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance record already exists for this date'
            ], 422);
        }

        $attendance = TeacherAttendance::create([
            'teacher_id' => $user->id,
            'course_id' => $request->course_id,
            'attendance_date' => $request->attendance_date,
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance record created successfully',
            'data' => $attendance
        ], 201);
    }

    /**
     * Display the specified attendance record.
     *
     * @param  \App\Models\TeacherAttendance  $attendance
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(TeacherAttendance $attendance)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $attendance->teacher_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $attendance->load(['teacher', 'course']);

        return response()->json([
            'status' => 'success',
            'data' => $attendance
        ]);
    }

    /**
     * Update the specified attendance record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TeacherAttendance  $attendance
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, TeacherAttendance $attendance)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $attendance->teacher_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'check_in_time' => 'required|date_format:H:i',
            'check_out_time' => 'required|date_format:H:i|after:check_in_time',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $attendance->update([
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance record updated successfully',
            'data' => $attendance
        ]);
    }

    /**
     * Remove the specified attendance record from storage.
     *
     * @param  \App\Models\TeacherAttendance  $attendance
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(TeacherAttendance $attendance)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $attendance->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance record deleted successfully'
        ]);
    }

    /**
     * Get attendance statistics for a teacher.
     *
     * @param  \App\Models\User  $teacher
     * @return \Illuminate\Http\JsonResponse
     */
    public function teacherStats(User $teacher)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin') && $user->id !== $teacher->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Get attendance records for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $attendances = TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        $totalHours = 0;
        foreach ($attendances as $attendance) {
            $checkIn = Carbon::parse($attendance->check_in_time);
            $checkOut = Carbon::parse($attendance->check_out_time);
            $totalHours += $checkOut->diffInHours($checkIn);
        }

        $stats = [
            'total_days' => $attendances->count(),
            'total_hours' => $totalHours,
            'average_hours_per_day' => $attendances->count() > 0 ? round($totalHours / $attendances->count(), 2) : 0,
            'attendance_percentage' => round(($attendances->count() / 30) * 100, 2),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'teacher' => $teacher,
                'statistics' => $stats,
                'attendances' => $attendances
            ]
        ]);
    }

    /**
     * Get attendance statistics for a course.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseStats(Course $course)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $attendances = TeacherAttendance::where('course_id', $course->id)
            ->with('teacher')
            ->get()
            ->groupBy('teacher_id');

        $teacherStats = [];
        foreach ($attendances as $teacherId => $teacherAttendances) {
            $totalHours = 0;
            foreach ($teacherAttendances as $attendance) {
                $checkIn = Carbon::parse($attendance->check_in_time);
                $checkOut = Carbon::parse($attendance->check_out_time);
                $totalHours += $checkOut->diffInHours($checkIn);
            }

            $teacherStats[] = [
                'teacher' => $teacherAttendances->first()->teacher,
                'total_days' => $teacherAttendances->count(),
                'total_hours' => $totalHours,
                'average_hours_per_day' => round($totalHours / $teacherAttendances->count(), 2),
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'course' => $course,
                'teacher_statistics' => $teacherStats
            ]
        ]);
    }
} 