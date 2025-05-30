<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Models\Course;
use App\Models\Group;
use App\Models\Schedule;
use Carbon\Carbon;

class StudentAttendanceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Teacher');
    }

    /**
     * Display a listing of student attendance records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $teacher = auth()->user();
        $courseId = $request->input('course_id');
        $groupId = $request->input('group_id');
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        // Get courses taught by this teacher using the many-to-many relationship
        $courses = $teacher->courses;
        
        // Filter attendances by course if selected
        $query = StudentAttendance::where('teacher_id', $teacher->id)
            ->with(['student', 'schedule.course', 'schedule.group']);
            
        if ($courseId) {
            $query->whereHas('schedule.course', function($q) use ($courseId) {
                $q->where('id', $courseId);
            });
        }
        
        if ($groupId) {
            $query->whereHas('schedule.group', function($q) use ($groupId) {
                $q->where('id', $groupId);
            });
        }
        
        if ($date) {
            $query->whereDate('attendance_date', $date);
        }
        
        // Group attendance records by schedule and date
        $attendances = $query->select('schedule_id', 'attendance_date', 
                \DB::raw('COUNT(CASE WHEN status = "present" THEN 1 END) as present_count'), 
                \DB::raw('COUNT(CASE WHEN status = "absent" THEN 1 END) as absent_count'))
            ->groupBy('schedule_id', 'attendance_date')
            ->orderBy('attendance_date', 'desc')
            ->paginate(10);
        
        return view('teacher.attendance.index', compact('attendances', 'courses', 'courseId', 'groupId', 'date'));
    }

    /**
     * Show the form for creating a new attendance record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $teacher = auth()->user();
        $scheduleId = $request->input('schedule_id');
        $courseId = $request->input('course_id');
        $groupId = $request->input('group_id');
        $attendanceDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        
        // Get courses taught by this teacher
        $courses = Course::where('teacher_id', $teacher->id)->get();
        
        // If schedule ID is provided
        if ($scheduleId) {
            $schedule = Schedule::findOrFail($scheduleId);
            $courseId = $schedule->course_id;
            $groupId = $schedule->group_id;
        }
        
        // Get students for the selected group if specified
        $students = [];
        if ($groupId) {
            $students = User::whereHas('roles', function($q) {
                    $q->where('name', 'Student');
                })
                ->whereHas('groups', function($q) use ($groupId) {
                    $q->where('group_id', $groupId);
                })
                ->get();
        }
        
        return view('teacher.attendance.create', compact(
            'courses', 'courseId', 'groupId', 'attendanceDate', 'students', 'scheduleId'
        ));
    }

    /**
     * Store a newly created attendance record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'group_id' => 'required|exists:groups,id',
            'attendance_date' => 'required|date',
            'students' => 'required|array',
            'status' => 'required|array',
            'notes' => 'nullable|array',
        ]);
        
        $teacher = auth()->user();
        $courseId = $request->course_id;
        $groupId = $request->group_id;
        $date = $request->attendance_date;
        
        // Get or create schedule
        $schedule = Schedule::firstOrCreate(
            [
                'course_id' => $courseId,
                'group_id' => $groupId,
                'day' => Carbon::parse($date)->format('l')
            ],
            [
                'start_time' => '08:00:00',
                'end_time' => '10:00:00'
            ]
        );
        
        // Delete any existing attendance for this schedule on this date
        StudentAttendance::where('schedule_id', $schedule->id)
            ->whereDate('date', $date)
            ->delete();
        
        // Create attendance records for each student
        foreach ($request->students as $studentId) {
            StudentAttendance::create([
                'schedule_id' => $schedule->id,
                'student_id' => $studentId,
                'teacher_id' => $teacher->id,
                'date' => $date,
                'status' => $request->status[$studentId] ?? 'absent',
                'notes' => $request->notes[$studentId] ?? null,
            ]);
        }
        
        return redirect()->route('teacher.attendance')
            ->with('success', 'تم تسجيل حضور الطلاب بنجاح');
    }

    /**
     * Display the specified attendance record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $attendance = StudentAttendance::select('schedule_id', 'date')
            ->where('id', $id)
            ->firstOrFail();
        
        $details = StudentAttendance::where('schedule_id', $attendance->schedule_id)
            ->where('date', $attendance->date)
            ->with(['schedule.course', 'schedule.group', 'student'])
            ->get();
        
        if (count($details) === 0) {
            abort(404);
        }
        
        $course = $details->first()->schedule->course;
        $group = $details->first()->schedule->group;
        
        return view('teacher.attendance.show', compact('details', 'course', 'group', 'attendance'));
    }

    /**
     * Display attendance report for a specific course.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function courseReport(Course $course)
    {
        // Verify that the authenticated teacher is associated with this course
        $teacher = auth()->user();
        if (!$teacher->courses->contains($course)) {
            abort(403, 'Unauthorized action.');
        }

        // Get attendance records for this course
        $attendanceRecords = StudentAttendance::where('course_id', $course->id)
            ->with(['student', 'group'])
            ->get();

        // Group attendance by date and calculate statistics
        $groupedAttendance = $attendanceRecords->groupBy('attendance_date')->map(function ($records) {
            $total = $records->count();
            $present = $records->where('status', 'present')->count();
            $absent = $records->where('status', 'absent')->count();
            $attendanceRate = $total > 0 ? ($present / $total) * 100 : 0;

            return [
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'attendance_rate' => $attendanceRate
            ];
        });

        // Calculate overall statistics
        $totalRecords = $attendanceRecords->count();
        $totalPresent = $attendanceRecords->where('status', 'present')->count();
        $totalAbsent = $attendanceRecords->where('status', 'absent')->count();
        $overallAttendanceRate = $totalRecords > 0 ? ($totalPresent / $totalRecords) * 100 : 0;

        return view('teacher.attendance.course-report', compact(
            'course',
            'groupedAttendance',
            'totalRecords',
            'totalPresent',
            'totalAbsent',
            'overallAttendanceRate'
        ));
    }

    public function studentReport()
    {
        $teacher = auth()->user();
        $students = User::where('role', 'student')
            ->whereHas('groups', function ($query) use ($teacher) {
                $query->whereHas('courses', function ($q) use ($teacher) {
                    $q->whereIn('id', $teacher->courses->pluck('id'));
                });
            })
            ->get();

        $courses = $teacher->courses;

        return view('teacher.attendance.student-report', compact('students', 'courses'));
    }

    public function dateReport()
    {
        $teacher = auth()->user();
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        // Get attendance records for the specified date range
        $query = StudentAttendance::whereHas('course', function($q) use ($teacher) {
                $q->whereIn('id', $teacher->courses->pluck('id'));
            })
            ->with(['student', 'course', 'group']);

        if ($dateFrom && $dateTo) {
            $query->whereBetween('attendance_date', [$dateFrom, $dateTo]);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->get();

        // Group attendance by date
        $groupedAttendance = $attendances->groupBy('attendance_date')->map(function($records) {
            $total = $records->count();
            $present = $records->where('status', 'present')->count();
            $absent = $records->where('status', 'absent')->count();
            $attendanceRate = $total > 0 ? ($present / $total) * 100 : 0;

            return [
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'attendance_rate' => $attendanceRate,
                'records' => $records
            ];
        });

        // Calculate overall statistics
        $totalRecords = $attendances->count();
        $totalPresent = $attendances->where('status', 'present')->count();
        $totalAbsent = $attendances->where('status', 'absent')->count();
        $overallAttendanceRate = $totalRecords > 0 ? ($totalPresent / $totalRecords) * 100 : 0;

        return view('teacher.attendance.date-report', compact(
            'groupedAttendance',
            'totalRecords',
            'totalPresent',
            'totalAbsent',
            'overallAttendanceRate',
            'dateFrom',
            'dateTo'
        ));
    }
} 