<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocationAttendance;
use App\Models\LocationSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LocationAttendanceController extends Controller
{
    /**
     * عرض سجل الحضور المكاني للطلاب والمعلمين
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // الحصول على التاريخ المطلوب أو استخدام اليوم الحالي
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        
        // الحصول على نوع المستخدم المطلوب (طالب أو معلم) أو الكل
        $userType = $request->input('user_type', 'all');
        
        // بناء استعلام للحضور
        $query = LocationAttendance::with(['user', 'locationSetting'])
            ->when($date, function ($query) use ($date) {
                return $query->whereDate('attendance_date', $date);
            });
        
        // تطبيق فلتر نوع المستخدم
        if ($userType !== 'all') {
            $query->whereHas('user.roles', function ($q) use ($userType) {
                $q->where('name', $userType);
            });
        }
        
        // الحصول على إجمالي إحصائيات الحضور لليوم المحدد
        $totalAttendance = LocationAttendance::whereDate('attendance_date', $date)->count();
        $totalPresentCount = LocationAttendance::whereDate('attendance_date', $date)
            ->where('is_within_range', true)
            ->count();
        $totalOutsideRangeCount = LocationAttendance::whereDate('attendance_date', $date)
            ->where('is_within_range', false)
            ->count();
        
        // استعلام النتائج النهائية مع الترتيب حسب الوقت
        $attendanceRecords = $query->orderBy('attendance_date', 'desc')
            ->orderBy('attendance_time', 'desc')
            ->paginate(20);
        
        // الحصول على قائمة المواقع
        $locations = LocationSetting::all();
        
        return view('admin.attendance.location-index', [
            'attendanceRecords' => $attendanceRecords,
            'date' => $date,
            'userType' => $userType,
            'locations' => $locations,
            'totalAttendance' => $totalAttendance,
            'totalPresentCount' => $totalPresentCount,
            'totalOutsideRangeCount' => $totalOutsideRangeCount
        ]);
    }
    
    /**
     * عرض تفاصيل الحضور لمستخدم معين
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function userDetails($userId)
    {
        $user = User::findOrFail($userId);
        
        $attendanceRecords = LocationAttendance::with('locationSetting')
            ->where('user_id', $userId)
            ->orderBy('attendance_date', 'desc')
            ->orderBy('attendance_time', 'desc')
            ->paginate(20);
        
        // إحصائيات الحضور للمستخدم
        $totalAttendance = LocationAttendance::where('user_id', $userId)->count();
        $totalPresentCount = LocationAttendance::where('user_id', $userId)
            ->where('is_within_range', true)
            ->count();
        $totalOutsideRangeCount = LocationAttendance::where('user_id', $userId)
            ->where('is_within_range', false)
            ->count();
        
        return view('admin.attendance.location-user-details', [
            'user' => $user,
            'attendanceRecords' => $attendanceRecords,
            'totalAttendance' => $totalAttendance,
            'totalPresentCount' => $totalPresentCount,
            'totalOutsideRangeCount' => $totalOutsideRangeCount
        ]);
    }
    
    /**
     * عرض تفاصيل الحضور لموقع معين
     *
     * @param  int  $locationId
     * @return \Illuminate\Http\Response
     */
    public function locationDetails($locationId)
    {
        $location = LocationSetting::findOrFail($locationId);
        
        $attendanceRecords = LocationAttendance::with('user')
            ->where('location_setting_id', $locationId)
            ->orderBy('attendance_date', 'desc')
            ->orderBy('attendance_time', 'desc')
            ->paginate(20);
        
        // إحصائيات الحضور للموقع
        $totalAttendance = LocationAttendance::where('location_setting_id', $locationId)->count();
        $totalPresentCount = LocationAttendance::where('location_setting_id', $locationId)
            ->where('is_within_range', true)
            ->count();
        $totalOutsideRangeCount = LocationAttendance::where('location_setting_id', $locationId)
            ->where('is_within_range', false)
            ->count();
        
        return view('admin.attendance.location-details', [
            'location' => $location,
            'attendanceRecords' => $attendanceRecords,
            'totalAttendance' => $totalAttendance,
            'totalPresentCount' => $totalPresentCount,
            'totalOutsideRangeCount' => $totalOutsideRangeCount
        ]);
    }
} 