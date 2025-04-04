<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Course;
use App\Models\Group;
use App\Models\Schedule;
use App\Models\AdminRequest;
use App\Models\TeacherAttendance;
use App\Models\StudentAttendance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            return $this->adminDashboard();
        } elseif ($user->hasRole('Teacher')) {
            return $this->teacherDashboard();
        } else {
            return $this->studentDashboard();
        }
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    private function adminDashboard()
    {
        // Basic statistics for admin dashboard
        $stats = [
            'studentsCount' => User::whereHas('roles', function($query) {
                $query->where('name', 'Student');
                    })->count(),
            'teachersCount' => User::whereHas('roles', function($query) {
                $query->where('name', 'Teacher');
                    })->count(),
            'coursesCount' => Course::count(),
            'groupsCount' => Group::count(),
        ];

        // Get pending admin requests
        $pendingRequests = AdminRequest::with('student')
            ->where('status', 'pending')
            ->orderBy('request_date', 'desc')
            ->take(10)
            ->get();

        // Today's teacher attendance
        $teachersAttendance = [];
        $today = Carbon::today()->format('Y-m-d');
        $teachers = User::whereHas('roles', function($query) {
            $query->where('name', 'Teacher');
        })->get();
        
        foreach ($teachers as $teacher) {
            $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
                ->whereDate('attendance_date', $today)
                ->first();
            
            if ($attendance) {
                $teachersAttendance[] = [
                    'teacher' => $teacher,
                    'status' => $attendance->status,
                    'notes' => $attendance->notes,
                    'attendance_id' => $attendance->id
                ];
            } else {
                $teachersAttendance[] = [
                    'teacher' => $teacher,
                    'status' => 'not_recorded',
                    'notes' => null,
                    'attendance_id' => null
                ];
            }
        }

        // Due fees students
        $dueFeesStudents = \DB::table('fees')
            ->select('fees.*', 'users.name as student_name')
            ->join('users', 'fees.user_id', '=', 'users.id')
            ->whereRaw('total_amount > paid_amount')
            ->take(10)
            ->get();

        // Recent messages sent by admin
        $recentMessages = \DB::table('messages')
            ->select('messages.*', 'users.name as receiver_name', 'roles.name as receiver_type')
            ->join('users', 'messages.receiver_id', '=', 'users.id')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('messages.sender_id', Auth::id())
            ->orderBy('messages.created_at', 'desc')
            ->take(10)
            ->get();

        // Quick notifications for admin dashboard
        $quickNotifications = [];

        // Add notification for pending requests
        if ($pendingRequests->count() > 0) {
            $quickNotifications[] = [
                'type' => 'request',
                'message' => 'لديك ' . $pendingRequests->count() . ' طلب إداري معلق بحاجة للمراجعة',
                'date' => Carbon::now()->subHours(2),
                'link' => route('admin.requests')
            ];
        }

        // Add notification for absent teachers
        $absentTeachersCount = TeacherAttendance::whereDate('attendance_date', $today)
            ->where('status', 'absent')
            ->count();

        if ($absentTeachersCount > 0) {
            $quickNotifications[] = [
                'type' => 'attendance',
                'message' => 'هناك ' . $absentTeachersCount . ' دكتور متغيب اليوم',
                'date' => Carbon::now()->subHours(1),
                'link' => route('admin.attendance')
            ];
        }

        // Add notification for due fees
        if ($dueFeesStudents->count() > 0) {
            $quickNotifications[] = [
                'type' => 'fee',
                'message' => 'هناك ' . $dueFeesStudents->count() . ' طالب لديهم رسوم متأخرة',
                'date' => Carbon::now()->subHours(5),
                'link' => route('admin.fees')
            ];
        }

        // Sort notifications by date (newest first)
        usort($quickNotifications, function($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });

        return view('admin.dashboard', compact(
            'stats',
            'pendingRequests',
            'teachersAttendance',
            'dueFeesStudents',
            'recentMessages',
            'quickNotifications'
        ));
    }

    /**
     * Show the teacher dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    private function teacherDashboard()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Basic statistics for teacher dashboard
        $teacherCourses = Course::where('teacher_id', $user->id)->with('groups')->get();
        $teacherGroupIds = $teacherCourses->pluck('groups')->flatten()->pluck('id')->unique();
        
        $stats = [
            'coursesCount' => $teacherCourses->count(),
            'groupsCount' => $teacherGroupIds->count(),
            'sessionsCount' => Schedule::whereIn('course_id', $teacherCourses->pluck('id'))
                ->where('day', $today->format('l'))
                ->count(),
            'unreadMessages' => \DB::table('messages')
                ->where('receiver_id', $user->id)
                ->whereNull('read_at')
                ->count()
        ];

        // Today's schedule for the teacher
        $todaySchedule = Schedule::whereIn('course_id', $teacherCourses->pluck('id'))
            ->where('day', $today->format('l'))
            ->with(['course', 'group'])
            ->orderBy('start_time')
            ->get();

        // Recent attendance records made by the teacher
        $recentAttendance = StudentAttendance::where('teacher_id', $user->id)
            ->select(\DB::raw('MIN(id) as id, schedule_id, date, COUNT(CASE WHEN status = "present" THEN 1 END) as present_count, COUNT(CASE WHEN status = "absent" THEN 1 END) as absent_count'))
            ->groupBy('schedule_id', 'date')
            ->orderBy('date', 'desc')
            ->with(['schedule.course', 'schedule.group'])
            ->take(10)
            ->get();

        // Recent messages received by the teacher
        $recentMessages = \DB::table('messages')
            ->join('users', 'messages.sender_id', '=', 'users.id')
            ->select('messages.*', 'users.name as sender_name')
            ->where('messages.receiver_id', $user->id)
            ->orderBy('messages.created_at', 'desc')
            ->take(5)
            ->get();

        // Teacher's attendance status today
        $attendanceStatus = null;
        $todayAttendance = TeacherAttendance::where('teacher_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first();
        
        if ($todayAttendance) {
            $attendanceStatus = $todayAttendance->status;
        }

        // Courses with group count for display
        $courses = Course::where('teacher_id', $user->id)
            ->withCount('groups')
            ->take(5)
            ->get();

        // Important notifications for teacher
        $notifications = [];

        // Schedule changes notification
        $recentScheduleChanges = Schedule::whereIn('course_id', $teacherCourses->pluck('id'))
            ->where('updated_at', '>', Carbon::now()->subDays(7))
            ->where('created_at', '<', Carbon::now()->subHours(24))
            ->count();
        
        if ($recentScheduleChanges > 0) {
            $notifications[] = [
                'type' => 'schedule',
                'message' => 'تم تحديث جدول محاضراتك مؤخراً، يرجى مراجعة الجدول الكامل',
                'date' => Carbon::now()->subHours(8),
                'link' => route('teacher.schedule')
            ];
        }

        // Check if there are new messages
        if ($stats['unreadMessages'] > 0) {
            $notifications[] = [
                'type' => 'message',
                'message' => 'لديك ' . $stats['unreadMessages'] . ' رسائل جديدة غير مقروءة',
                'date' => Carbon::now()->subHours(2),
                'link' => route('teacher.messages')
            ];
        }

        // If teacher has classes today
        if ($todaySchedule->count() > 0) {
            $notifications[] = [
                'type' => 'schedule',
                'message' => 'لديك ' . $todaySchedule->count() . ' محاضرات مجدولة اليوم',
                'date' => Carbon::today(),
                'link' => route('teacher.schedule')
            ];
        }

        // Sort notifications by date (newest first)
        usort($notifications, function($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });

        return view('teacher.dashboard', compact(
            'stats',
            'todaySchedule',
            'recentAttendance',
            'recentMessages',
            'attendanceStatus',
            'courses',
            'notifications'
        ));
    }

    /**
     * Show the student dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    private function studentDashboard()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        // Check if student has a group
        $student = User::with('group')->find($user->id);
        $groupId = $student->group_id;
        
        // Basic statistics for student dashboard
        $stats = [
            'coursesCount' => $groupId ? Course::whereHas('groups', function($query) use ($groupId) {
                $query->where('groups.id', $groupId);
            })->count() : 0,
            'sessionsCount' => $groupId ? Schedule::where('group_id', $groupId)
                ->where('day', $today->format('l'))
                ->count() : 0,
            'requestsCount' => AdminRequest::where('user_id', $user->id)->count(),
            'unreadMessages' => \DB::table('messages')
                ->where('receiver_id', $user->id)
                ->whereNull('read_at')
                ->count()
        ];

        // Today's schedule for the student
        $todaySchedule = $groupId ? Schedule::where('group_id', $groupId)
            ->where('day', $today->format('l'))
            ->with(['course.teacher'])
            ->orderBy('start_time')
            ->get() : collect();

        // Student's courses
        $courses = $groupId ? Course::whereHas('groups', function($query) use ($groupId) {
            $query->where('groups.id', $groupId);
        })->with('teacher')->get() : collect();

        // Recent requests made by the student
        $recentRequests = AdminRequest::where('user_id', $user->id)
            ->orderBy('request_date', 'desc')
            ->take(5)
            ->get();

        // Student's fee status
        $feeStatus = null;
        $studentFee = \DB::table('fees')->where('user_id', $user->id)->first();
        
        if ($studentFee) {
            $feeStatus = [
                'total_amount' => $studentFee->total_amount,
                'paid_amount' => $studentFee->paid_amount,
                'remaining_amount' => $studentFee->total_amount - $studentFee->paid_amount,
                'paid_percentage' => $studentFee->total_amount > 0 
                    ? round(($studentFee->paid_amount / $studentFee->total_amount) * 100) 
                    : 0,
                'due_date' => $studentFee->due_date,
            ];
        }

        // Recent messages received by the student
        $recentMessages = \DB::table('messages')
            ->join('users', 'messages.sender_id', '=', 'users.id')
            ->select('messages.*', 'users.name as sender_name')
            ->where('messages.receiver_id', $user->id)
            ->orderBy('messages.created_at', 'desc')
            ->take(5)
            ->get();

        // Important notifications for student
        $notifications = [];

        // Schedule changes notification
        if ($groupId) {
            $recentScheduleChanges = Schedule::where('group_id', $groupId)
                ->where('updated_at', '>', Carbon::now()->subDays(7))
                ->where('created_at', '<', Carbon::now()->subHours(24))
                ->count();
            
            if ($recentScheduleChanges > 0) {
                $notifications[] = [
                    'type' => 'schedule',
                    'message' => 'تم تحديث جدول محاضراتك مؤخراً، يرجى مراجعة الجدول الكامل',
                    'date' => Carbon::now()->subHours(8),
                    'link' => route('student.schedule')
                ];
            }
        }

        // Check if there are new messages
        if ($stats['unreadMessages'] > 0) {
            $notifications[] = [
                'type' => 'message',
                'message' => 'لديك ' . $stats['unreadMessages'] . ' رسائل جديدة غير مقروءة',
                'date' => Carbon::now()->subHours(2),
                'link' => route('student.messages')
            ];
        }

        // Request status change notification
        $recentStatusChange = AdminRequest::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->where('updated_at', '>', Carbon::now()->subDays(3))
            ->first();
        
        if ($recentStatusChange) {
            $status = $recentStatusChange->status == 'approved' ? 'تمت الموافقة على' : 'تم رفض';
            $notifications[] = [
                'type' => 'request',
                'message' => $status . ' طلبك الأخير: ' . $recentStatusChange->getTypeNameAttribute(),
                'date' => $recentStatusChange->updated_at,
                'link' => route('student.requests.show', $recentStatusChange->id)
            ];
        }

        // Fee reminder notification
        if ($feeStatus && $feeStatus['paid_percentage'] < 100) {
            $notifications[] = [
                'type' => 'fee',
                'message' => 'تذكير: عليك سداد مبلغ ' . number_format($feeStatus['remaining_amount'], 2) . ' ريال من الرسوم الدراسية',
                'date' => Carbon::now()->subDays(1),
                'link' => route('student.fees')
            ];
        }

        // Sort notifications by date (newest first)
        usort($notifications, function($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });

        return view('student.dashboard', compact(
            'stats',
            'todaySchedule',
            'courses',
            'recentRequests',
            'feeStatus',
            'recentMessages',
            'notifications'
        ));
    }
}
