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
        
        $query = TeacherAttendance::with(['teacher']);

        // If teacher, only show their own attendance
        if ($user->hasRole('Teacher')) {
            $query->where('teacher_id', $user->id);
        }
        // If admin and teacher_id is provided
        elseif ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('attendance_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('attendance_date', '<=', $request->end_date);
        }
        if ($request->has('date')) {
            $query->whereDate('attendance_date', $request->date);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
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

        if (!$user->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date|before_or_equal:today',
            'check_in' => 'nullable|date_format:Y-m-d H:i:s',
            'check_out' => 'nullable|date_format:Y-m-d H:i:s|after:check_in',
            'status' => 'required|in:present,absent,late,excused,on_leave',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if teacher exists and has teacher role
        $teacher = User::find($request->teacher_id);
        if (!$teacher->hasRole('Teacher')) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not a teacher'
            ], 422);
        }

        // Check for duplicate attendance record
        $existingAttendance = TeacherAttendance::where('teacher_id', $request->teacher_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance record already exists for this teacher on this date'
            ], 422);
        }

        $attendance = TeacherAttendance::create([
            'teacher_id' => $request->teacher_id,
            'attendance_date' => $request->attendance_date,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'status' => $request->status,
            'notes' => $request->notes,
            'recorded_by' => $user->id,
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

        $attendance->load(['teacher']);

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
            'check_in' => 'nullable|date_format:Y-m-d H:i:s',
            'check_out' => 'nullable|date_format:Y-m-d H:i:s|after:check_in',
            'status' => 'required|in:present,absent,late,excused,on_leave',
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
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'status' => $request->status,
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
        $presentDays = 0;
        
        foreach ($attendances as $attendance) {
            if ($attendance->check_in && $attendance->check_out) {
                $checkIn = Carbon::parse($attendance->check_in);
                $checkOut = Carbon::parse($attendance->check_out);
                $totalHours += $checkOut->diffInHours($checkIn);
            }
            
            if (in_array($attendance->status, ['present', 'late'])) {
                $presentDays++;
            }
        }

        $stats = [
            'total_days_recorded' => $attendances->count(),
            'present_days' => $presentDays,
            'absent_days' => $attendances->where('status', 'absent')->count(),
            'late_days' => $attendances->where('status', 'late')->count(),
            'total_hours' => $totalHours,
            'average_hours_per_day' => $presentDays > 0 ? round($totalHours / $presentDays, 2) : 0,
            'attendance_percentage' => $attendances->count() > 0 ? round(($presentDays / $attendances->count()) * 100, 2) : 0,
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'teacher' => $teacher,
                'statistics' => $stats,
                'recent_attendances' => $attendances->take(10)
            ]
        ]);
    }
} 