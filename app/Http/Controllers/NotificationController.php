<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminRequest;
use App\Models\Message;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\Fee;
use Carbon\Carbon;
use App\Models\User;

class NotificationController extends Controller
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
     * Display notifications for admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function adminIndex()
    {
        // Get admin notifications (pending requests, teacher absences, etc.)
        $notifications = [];
        $today = Carbon::today();

        // Add notification for pending requests
        $pendingRequests = AdminRequest::where('status', 'pending')->count();
        if ($pendingRequests > 0) {
            $notifications[] = [
                'id' => 'req-' . uniqid(),
                'type' => 'request',
                'message' => 'لديك ' . $pendingRequests . ' طلب إداري معلق بحاجة للمراجعة',
                'date' => Carbon::now()->subHours(2),
                'link' => route('admin.requests'),
                'is_read' => false
            ];
        }

        // Add notification for teacher absences
        $teacherAbsences = \App\Models\TeacherAttendance::whereDate('date', $today)
            ->where('status', 'absent')
            ->count();
            
        if ($teacherAbsences > 0) {
            $notifications[] = [
                'id' => 'att-' . uniqid(),
                'type' => 'attendance',
                'message' => 'هناك ' . $teacherAbsences . ' دكتور متغيب اليوم',
                'date' => Carbon::now()->subHours(1),
                'link' => route('admin.attendance'),
                'is_read' => false
            ];
        }

        // Add notification for upcoming events
        $upcomingEvents = Schedule::whereBetween('date', [$today, $today->copy()->addDays(3)])
            ->count();
            
        if ($upcomingEvents > 0) {
            $notifications[] = [
                'id' => 'evt-' . uniqid(),
                'type' => 'schedule',
                'message' => 'هناك ' . $upcomingEvents . ' محاضرة في الأيام الثلاثة القادمة',
                'date' => Carbon::now()->subDays(1),
                'link' => route('schedules.index'),
                'is_read' => false
            ];
        }

        // Sort notifications by date (newest first)
        usort($notifications, function($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Display notifications for teacher.
     *
     * @return \Illuminate\Http\Response
     */
    public function teacherIndex()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $notifications = [];

        // Check for unread messages
        $unreadMessages = Message::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();
            
        if ($unreadMessages > 0) {
            $notifications[] = [
                'id' => 'msg-' . uniqid(),
                'type' => 'message',
                'message' => 'لديك ' . $unreadMessages . ' رسائل جديدة غير مقروءة',
                'date' => Carbon::now()->subHours(2),
                'link' => route('teacher.messages'),
                'is_read' => false
            ];
        }

        // Check for today's classes
        $teacherCourses = Course::where('teacher_id', $user->id)->pluck('id')->toArray();
        $todayClasses = Schedule::whereIn('course_id', $teacherCourses)
            ->whereDate('date', $today)
            ->count();
            
        if ($todayClasses > 0) {
            $notifications[] = [
                'id' => 'sch-' . uniqid(),
                'type' => 'schedule',
                'message' => 'لديك ' . $todayClasses . ' محاضرات مجدولة اليوم',
                'date' => Carbon::today(),
                'link' => route('teacher.schedule'),
                'is_read' => false
            ];
        }

        // Sort notifications by date (newest first)
        usort($notifications, function($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });

        return view('teacher.notifications.index', compact('notifications'));
    }

    /**
     * Display a listing of the notifications for the logged-in student.
     *
     * @return \Illuminate\Http\Response
     */
    public function studentIndex()
    {
        $user = Auth::user();
        
        if ($user->role !== 'Student') {
            return redirect()->route('dashboard')->with('error', 'غير مصرح بالوصول');
        }
        
        // For demo purposes, we'll create sample notifications
        // In a real application, these would come from a database table
        $notifications = [
            [
                'id' => 1,
                'type' => 'schedule',
                'message' => 'تم تحديث جدولك الدراسي للفصل الحالي',
                'date' => Carbon::now()->subDays(1),
                'is_read' => false,
                'link' => route('student.schedule')
            ],
            [
                'id' => 2,
                'type' => 'message',
                'message' => 'رسالة جديدة من الإدارة بخصوص امتحانات منتصف الفصل',
                'date' => Carbon::now()->subDays(3),
                'is_read' => true,
                'link' => '#'
            ],
            [
                'id' => 3,
                'type' => 'request',
                'message' => 'تم الموافقة على طلب الغياب الخاص بك',
                'date' => Carbon::now()->subDays(5),
                'is_read' => true,
                'link' => '#'
            ],
            [
                'id' => 4,
                'type' => 'fee',
                'message' => 'تذكير: موعد سداد الرسوم الدراسية خلال أسبوع',
                'date' => Carbon::now()->subHours(12),
                'is_read' => false,
                'link' => '#'
            ]
        ];
        
        return view('student.notifications.index', compact('notifications'));
    }

    /**
     * Mark a notification as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function markAsRead($id)
    {
        // In a real application, you would update the database record
        
        return redirect()->back()->with('success', 'تم تحديث حالة الإشعار');
    }
}
