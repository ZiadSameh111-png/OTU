<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Models\Course;
use App\Models\AdminRequest;
use App\Models\TeacherAttendance;
use App\Models\Fee;
use App\Models\Message;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
     * Redirect to the appropriate dashboard based on user role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->roles()->first();

        if (!$role) {
            return redirect()->route('login')
                ->with('error', 'لم يتم تعيين دور لحسابك. الرجاء التواصل مع مدير النظام.');
        }

        switch ($role->name) {
            case 'Admin':
                return $this->adminDashboard($user);

            case 'Teacher':
                return $this->teacherDashboard($user);

            case 'Student':
                return $this->studentDashboard($user);

            default:
                return redirect()->route('login')
                    ->with('error', 'عذراً، لا يمكن الوصول إلى لوحة التحكم.');
        }
    }

    /**
     * لوحة تحكم المسؤول
     */
    private function adminDashboard($user)
    {
        $data = [
            'user' => $user,
            'totalUsers' => User::count(),
            'totalStudents' => User::whereHas('roles', function($q) {
                $q->where('name', 'Student');
            })->count(),
            'totalTeachers' => User::whereHas('roles', function($q) {
                $q->where('name', 'Teacher');
            })->count(),
        ];

        // إضافة بيانات المجموعات إذا كان الجدول موجودًا
        if (Schema::hasTable('groups')) {
            $data['totalGroups'] = Group::count();
            $data['activeGroups'] = Group::where('active', true)->count();
        }

        // إضافة بيانات المقررات إذا كان الجدول موجودًا
        if (Schema::hasTable('courses')) {
            $data['totalCourses'] = Course::count();
        }

        // إضافة بيانات الطلبات إذا كان الجدول موجودًا
        if (Schema::hasTable('admin_requests')) {
            $data['pendingRequests'] = AdminRequest::where('status', 'pending')->count();
        }
        
        return view('dashboards.admin', $data);
    }

    /**
     * لوحة تحكم المعلم
     */
    private function teacherDashboard($user)
    {
        $data = [
            'user' => $user,
        ];

        // إضافة بيانات الطلاب إذا كان هناك علاقة
        if (method_exists($user, 'teacherCourses')) {
            $data['courses'] = $user->teacherCourses()->with('groups')->get();
            $data['totalCourses'] = $user->teacherCourses()->count();
            
            // Get all students (use a direct query instead of relying on course-group relationships)
            $data['students'] = User::whereHas('roles', function($q) {
                $q->where('name', 'Student');
            })->paginate(10);
        } else {
            $data['students'] = User::whereHas('roles', function($q) {
                $q->where('name', 'Student');
            })->where('id', 0)->paginate(10); // Empty result set with pagination
        }

        // إضافة بيانات الحضور إذا كان الجدول موجوداً
        if (Schema::hasTable('teacher_attendances')) {
            $data['attendanceRecords'] = TeacherAttendance::where('user_id', $user->id)
                ->orderBy('attendance_date', 'desc')
                ->limit(5)
                ->get();
        }
        
        // إضافة بيانات الرسائل إذا كان الجدول موجوداً
        if (Schema::hasTable('messages')) {
            $data['unreadMessagesCount'] = Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();
        }
        
        return view('dashboards.teacher', $data);
    }

    /**
     * لوحة تحكم الطالب
     */
    private function studentDashboard($user)
    {
        $data = [
            'user' => $user,
        ];

        // إضافة بيانات المجموعة إذا كانت هناك علاقة
        if ($user->group) {
            $data['group'] = $user->group;
        }

        // إضافة بيانات المقررات إذا كانت هناك علاقة
        if (method_exists($user, 'studentCourses')) {
            // تحقق من وجود العلاقة وأن المستخدم مرتبط بمجموعة
            if ($user->group) {
                $data['courses'] = $user->studentCourses()->get();
            } else {
                $data['courses'] = collect([]);  // إرجاع مجموعة فارغة إذا لم يكن المستخدم مرتبطًا بمجموعة
            }
        }

        // إضافة بيانات الرسوم إذا كان الجدول موجوداً
        if (Schema::hasTable('fees')) {
            $data['fees'] = Fee::where('user_id', $user->id)->get();
            $data['pendingFees'] = Fee::where('user_id', $user->id)
                ->where('status', '!=', 'paid')
                ->count();
        }

        // إضافة بيانات الرسائل إذا كان الجدول موجوداً
        if (Schema::hasTable('messages')) {
            $data['unreadMessagesCount'] = Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();
        }
        
        return view('dashboards.student', $data);
    }
}
