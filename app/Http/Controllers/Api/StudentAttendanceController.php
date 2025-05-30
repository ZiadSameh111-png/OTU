<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Models\Course;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class StudentAttendanceController extends Controller
{
    /**
     * Display a listing of student attendance records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = StudentAttendance::with(['student', 'course']);

        if ($user->hasRole('Admin')) {
            // Admin can see all attendance records with optional filters
            if ($request->has('student_id')) {
                $query->where('student_id', $request->student_id);
            }
            if ($request->has('course_id')) {
                $query->where('course_id', $request->course_id);
            }
            if ($request->has('group_id')) {
                $query->whereHas('student', function($q) use ($request) {
                    $q->where('group_id', $request->group_id);
                });
            }
        } elseif ($user->hasRole('Teacher')) {
            // Teachers can only see attendance for their courses
            $query->whereHas('course', function($q) use ($user) {
                $q->whereHas('teachers', function($sq) use ($user) {
                    $sq->where('users.id', $user->id);
                });
            });
            
            if ($request->has('student_id')) {
                $query->where('student_id', $request->student_id);
            }
            if ($request->has('course_id')) {
                $query->where('course_id', $request->course_id);
            }
        } elseif ($user->hasRole('Student')) {
            // Students can only see their own attendance
            $query->where('student_id', $user->id);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
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
            'student_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:present,absent,late,excused',
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

        // Check if student is enrolled in this course
        $student = User::find($request->student_id);
        if (!$course->groups->contains($student->group_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student is not enrolled in this course'
            ], 422);
        }

        // Check for duplicate attendance record
        $existingAttendance = StudentAttendance::where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance record already exists for this student on this date'
            ], 422);
        }

        $attendance = StudentAttendance::create([
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
            'attendance_date' => $request->attendance_date,
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
     * Store multiple attendance records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBulk(Request $request)
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
            'attendances' => 'required|array|min:1',
            'attendances.*.student_id' => 'required|exists:users,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
            'attendances.*.notes' => 'nullable|string',
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

        $createdAttendances = [];
        $errors = [];

        foreach ($request->attendances as $attendanceData) {
            // Check if student is enrolled in this course
            $student = User::find($attendanceData['student_id']);
            if (!$course->groups->contains($student->group_id)) {
                $errors[] = "Student {$student->name} is not enrolled in this course";
                continue;
            }

            // Check for duplicate attendance record
            $existingAttendance = StudentAttendance::where('student_id', $attendanceData['student_id'])
                ->where('course_id', $request->course_id)
                ->whereDate('attendance_date', $request->attendance_date)
                ->first();

            if ($existingAttendance) {
                $errors[] = "Attendance record already exists for student {$student->name} on this date";
                continue;
            }

            $attendance = StudentAttendance::create([
                'student_id' => $attendanceData['student_id'],
                'course_id' => $request->course_id,
                'attendance_date' => $request->attendance_date,
                'status' => $attendanceData['status'],
                'notes' => $attendanceData['notes'] ?? null,
                'recorded_by' => $user->id,
            ]);

            $createdAttendances[] = $attendance;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance records processed',
            'data' => [
                'created' => $createdAttendances,
                'errors' => $errors
            ]
        ], !empty($createdAttendances) ? 201 : 422);
    }

    /**
     * Display the specified attendance record.
     *
     * @param  \App\Models\StudentAttendance  $attendance
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(StudentAttendance $attendance)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $attendance->course->teachers->contains($user->id)) &&
            !($user->hasRole('Student') && $attendance->student_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $attendance->load(['student', 'course', 'recordedBy']);

        return response()->json([
            'status' => 'success',
            'data' => $attendance
        ]);
    }

    /**
     * Update the specified attendance record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StudentAttendance  $attendance
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, StudentAttendance $attendance)
    {
        $user = Auth::user();

        if (!$user->hasRole('Teacher') || !$attendance->course->teachers->contains($user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:present,absent,late,excused',
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
            'status' => $request->status,
            'notes' => $request->notes,
            'recorded_by' => $user->id,
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
     * @param  \App\Models\StudentAttendance  $attendance
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(StudentAttendance $attendance)
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
     * Get attendance statistics for a student.
     *
     * @param  \App\Models\User  $student
     * @return \Illuminate\Http\JsonResponse
     */
    public function studentStats(User $student)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $user->teacherCourses()->whereHas('groups', function($q) use ($student) {
                $q->where('id', $student->group_id);
            })->exists()) &&
            !($user->id === $student->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $attendances = StudentAttendance::where('student_id', $student->id)
            ->with('course')
            ->get()
            ->groupBy('course_id');

        $courseStats = [];
        foreach ($attendances as $courseId => $courseAttendances) {
            $total = $courseAttendances->count();
            $present = $courseAttendances->where('status', 'present')->count();
            $absent = $courseAttendances->where('status', 'absent')->count();
            $late = $courseAttendances->where('status', 'late')->count();
            $excused = $courseAttendances->where('status', 'excused')->count();

            $courseStats[] = [
                'course' => $courseAttendances->first()->course,
                'total_classes' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'excused' => $excused,
                'attendance_percentage' => $total > 0 ? round((($present + $late) / $total) * 100, 2) : 0,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'student' => $student,
                'course_statistics' => $courseStats
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

        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $course->teachers->contains($user->id))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $attendances = StudentAttendance::where('course_id', $course->id)
            ->with('student')
            ->get()
            ->groupBy('student_id');

        $studentStats = [];
        foreach ($attendances as $studentId => $studentAttendances) {
            $total = $studentAttendances->count();
            $present = $studentAttendances->where('status', 'present')->count();
            $absent = $studentAttendances->where('status', 'absent')->count();
            $late = $studentAttendances->where('status', 'late')->count();
            $excused = $studentAttendances->where('status', 'excused')->count();

            $studentStats[] = [
                'student' => $studentAttendances->first()->student,
                'total_classes' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'excused' => $excused,
                'attendance_percentage' => $total > 0 ? round((($present + $late) / $total) * 100, 2) : 0,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'course' => $course,
                'student_statistics' => $studentStats
            ]
        ]);
    }

    /**
     * Get attendance statistics for a group.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function groupStats(Group $group)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $user->teacherCourses()->whereHas('groups', function($q) use ($group) {
                $q->where('id', $group->id);
            })->exists())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $students = $group->students;
        $studentStats = [];

        foreach ($students as $student) {
            $attendances = StudentAttendance::where('student_id', $student->id)
                ->whereHas('course', function($q) use ($group) {
                    $q->whereHas('groups', function($sq) use ($group) {
                        $sq->where('id', $group->id);
                    });
                })
                ->get();

            $total = $attendances->count();
            $present = $attendances->where('status', 'present')->count();
            $absent = $attendances->where('status', 'absent')->count();
            $late = $attendances->where('status', 'late')->count();
            $excused = $attendances->where('status', 'excused')->count();

            $studentStats[] = [
                'student' => $student,
                'total_classes' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'excused' => $excused,
                'attendance_percentage' => $total > 0 ? round((($present + $late) / $total) * 100, 2) : 0,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'group' => $group,
                'student_statistics' => $studentStats
            ]
        ]);
    }
} 