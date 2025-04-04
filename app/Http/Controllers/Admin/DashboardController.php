<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use App\Models\Group;
use App\Models\AdminRequest;
use App\Models\TeacherAttendance;
use App\Models\Fee;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        $this->middleware('role:Admin');
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // 1. الإحصائيات العامة - Statistics Overview
        $stats = [
            'studentsCount' => User::role('Student')->count(),
            'teachersCount' => User::role('Teacher')->count(),
            'coursesCount' => Course::count(),
            'groupsCount' => Group::count(),
        ];

        // 2. الطلبات الإدارية المعلقة - Pending Administrative Requests
        $pendingRequests = AdminRequest::with('student')
            ->where('status', 'pending')
            ->orderBy('priority')
            ->orderBy('request_date', 'desc')
            ->take(5)
            ->get();

        // 3. حضور الدكاترة اليوم - Today's Teacher Attendance
        $today = Carbon::today()->format('Y-m-d');
        
        // الحصول على جميع المدرسين
        $teachers = User::role('Teacher')->get();
        
        // الحصول على سجلات الحضور لليوم الحالي
        $todayAttendances = TeacherAttendance::whereDate('attendance_date', $today)
            ->get()
            ->keyBy('teacher_id');
        
        // إنشاء مصفوفة من المدرسين مع حالة الحضور الخاصة بهم
        $teachersAttendance = [];
        foreach ($teachers as $teacher) {
            $attendance = $todayAttendances->get($teacher->id);
            $teachersAttendance[] = [
                'teacher' => $teacher,
                'status' => $attendance ? $attendance->status : 'not_recorded',
                'notes' => $attendance ? $attendance->notes : null,
                'attendance_id' => $attendance ? $attendance->id : null,
            ];
        }
        
        // 4. الرسوم الدراسية المستحقة - Due Fees Overview
        $dueFeesStudents = Fee::where('status', '!=', 'paid')
            ->where('remaining_amount', '>', 0)
            ->with('student')
            ->orderBy('remaining_amount', 'desc')
            ->take(5)
            ->get();
        
        // 5. الرسائل الأخيرة - Recent Messages
        $recentMessages = Message::where('sender_id', Auth::id())
            ->with(['receiver'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // 6. الإشعارات السريعة - Quick Notifications
        $quickNotifications = $this->getQuickNotifications();
        
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
     * إنشاء إشعارات سريعة بناءً على بيانات النظام
     * 
     * @return array
     */
    private function getQuickNotifications()
    {
        $notifications = [];
        $today = Carbon::today();
        
        // إشعارات الطلبات الجديدة (أقل من 3 أيام)
        $newRequests = AdminRequest::with('student')
            ->where('status', 'pending')
            ->where('request_date', '>=', $today->copy()->subDays(3))
            ->orderBy('priority')
            ->orderBy('request_date', 'desc')
            ->take(3)
            ->get();
            
        foreach ($newRequests as $request) {
            $notifications[] = [
                'type' => 'request',
                'message' => "طلب جديد من الطالب {$request->student->name} في {$request->request_date->format('Y-m-d')}",
                'link' => route('admin.requests.show', $request->id),
                'date' => $request->request_date,
                'priority' => $request->priority
            ];
        }
        
        // إشعارات الحضور (المدرسين الغائبين اليوم)
        $absentTeachers = TeacherAttendance::with('teacher')
            ->whereDate('attendance_date', $today)
            ->where('status', 'absent')
            ->take(3)
            ->get();
            
        foreach ($absentTeachers as $attendance) {
            $notifications[] = [
                'type' => 'attendance',
                'message' => "المدرس {$attendance->teacher->name} غائب اليوم {$today->format('Y-m-d')}",
                'link' => route('admin.attendance'),
                'date' => $today,
                'priority' => 'high'
            ];
        }
        
        // إشعارات الرسوم المستحقة (المبلغ المتبقي كبير)
        $highRemainingFees = Fee::with('student')
            ->where('remaining_amount', '>', 1000)
            ->where('status', '!=', 'paid')
            ->orderBy('remaining_amount', 'desc')
            ->take(3)
            ->get();
            
        foreach ($highRemainingFees as $fee) {
            $notifications[] = [
                'type' => 'fee',
                'message' => "رسوم متبقية للطالب {$fee->student->name} بقيمة {$fee->remaining_amount} ريال",
                'link' => route('admin.fees.payments', $fee->id),
                'date' => now(),
                'priority' => 'normal'
            ];
        }
        
        // ترتيب الإشعارات حسب الأولوية والتاريخ
        $priorityOrder = [
            'urgent' => 1,
            'high' => 2,
            'normal' => 3,
            'low' => 4
        ];
        
        usort($notifications, function ($a, $b) use ($priorityOrder) {
            // الترتيب حسب الأولوية أولاً
            if ($priorityOrder[$a['priority']] !== $priorityOrder[$b['priority']]) {
                return $priorityOrder[$a['priority']] - $priorityOrder[$b['priority']];
            }
            
            // ثم حسب التاريخ (الأحدث أولاً)
            return $b['date']->timestamp - $a['date']->timestamp;
        });
        
        // أخذ أحدث 5 إشعارات
        return array_slice($notifications, 0, 5);
    }
}
