<?php

namespace App\Http\Controllers;

use App\Models\LocationAttendance;
use App\Models\LocationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationAttendanceController extends Controller
{
    /**
     * عرض صفحة تسجيل الحضور
     */
    public function index()
    {
        $user = Auth::user();
        $locations = LocationSetting::all();
        $todayAttendance = LocationAttendance::getTodayAttendance($user->id);
        
        return view('attendance.location.index', compact('locations', 'todayAttendance'));
    }
    
    /**
     * تخزين سجل الحضور
     */
    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:location_settings,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        
        $user = Auth::user();
        $locationId = $request->location_id;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        
        // التحقق مما إذا كان المستخدم قد سجل حضوره بالفعل اليوم لهذا الموقع
        if (LocationAttendance::hasAttendedToday($user->id, $locationId)) {
            return redirect()->back()->with('error', 'لقد قمت بتسجيل حضورك بالفعل لهذا الموقع اليوم');
        }
        
        // الحصول على معلومات الموقع
        $location = LocationSetting::findOrFail($locationId);
        
        // حساب المسافة بين موقع المستخدم والموقع المطلوب
        $distance = LocationSetting::calculateDistance(
            $location->latitude,
            $location->longitude,
            $latitude,
            $longitude
        );
        
        // التحقق مما إذا كان المستخدم ضمن النطاق المسموح به
        $isWithinRange = $distance <= $location->range_meters;
        
        // جمع معلومات الجهاز - نستخدم معلومات المتصفح الأساسية بدلاً من مكتبة Agent
        $userAgent = $request->header('User-Agent');
        $deviceInfo = $userAgent ? $userAgent : 'Unknown Device';
        
        // إنشاء سجل الحضور
        $attendance = new LocationAttendance([
            'user_id' => $user->id,
            'location_setting_id' => $locationId,
            'attendance_date' => now()->toDateString(),
            'attendance_time' => now()->toTimeString(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'distance_meters' => round($distance),
            'is_within_range' => $isWithinRange,
            'status' => $isWithinRange ? 'present' : 'outside_range',
            'notes' => $isWithinRange ? 'تم تسجيل الحضور بنجاح' : 'خارج النطاق المسموح به',
            'device_info' => $deviceInfo,
            'ip_address' => $request->ip(),
        ]);
        
        $attendance->save();
        
        $message = $isWithinRange
            ? 'تم تسجيل حضورك بنجاح'
            : 'تم تسجيل محاولة الحضور ولكنك خارج النطاق المسموح به. المسافة: ' . round($distance) . ' متر';
        
        $status = $isWithinRange ? 'success' : 'warning';
        
        return redirect()->back()->with($status, $message);
    }
    
    /**
     * عرض سجلات الحضور
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $date = $request->date ? $request->date : now()->toDateString();
        
        $query = LocationAttendance::with('locationSetting')
            ->where('user_id', $user->id);
            
        // تطبيق فلتر التاريخ إذا تم تحديده
        if ($request->has('date')) {
            $query->whereDate('attendance_date', $date);
        }
        
        $attendanceRecords = $query->orderBy('attendance_date', 'desc')
            ->orderBy('attendance_time', 'desc')
            ->paginate(20)
            ->appends($request->query());
        
        return view('attendance.location.history', compact('attendanceRecords', 'date'));
    }
    
    /**
     * الحصول على سجلات الحضور حسب التاريخ
     */
    public function getByDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);
        
        $user = Auth::user();
        $date = $request->date;
        
        $attendances = LocationAttendance::with('locationSetting')
            ->where('user_id', $user->id)
            ->whereDate('attendance_date', $date)
            ->orderBy('attendance_time')
            ->get();
        
        return view('attendance.location.by-date', compact('attendances', 'date'));
    }
}
