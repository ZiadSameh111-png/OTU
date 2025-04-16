<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherAttendance;
use App\Models\StudentAttendance;
use App\Models\LocationAttendance;
use App\Models\User;
use Carbon\Carbon;

class TeacherAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // الحصول على جميع المدرسين
        $teachers = User::whereHas('roles', function($query) {
            $query->where('name', 'Teacher');
        })->get();
        
        // الحصول على جميع الطلاب
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->get();
        
        // تحديد التاريخ المطلوب
        $date = $request->date ?? now()->format('Y-m-d');
        
        // دائماً عرض جميع سجلات الحضور (معلمين وطلاب)
        $userType = 'all';
        
        // استعلام سجلات حضور المعلمين
        $teacherAttendances = TeacherAttendance::with('teacher')
            ->whereDate('attendance_date', $date)
            ->orderBy('attendance_date', 'desc')
            ->get();
        
        // استعلام سجلات حضور الطلاب
        $studentAttendances = StudentAttendance::with('student')
            ->whereDate('attendance_date', $date)
            ->orderBy('attendance_date', 'desc')
            ->get();
            
        // استعلام سجلات الحضور المكاني (التي يسجلها الطلاب والمعلمين من حساباتهم)
        $locationAttendances = LocationAttendance::with(['user', 'locationSetting'])
            ->whereDate('attendance_date', $date)
            ->orderBy('attendance_date', 'desc')
            ->orderBy('attendance_time', 'desc')
            ->get();
        
        // دمج النتائج
        $allAttendances = collect();
        
        foreach ($teacherAttendances as $attendance) {
            $allAttendances->push([
                'id' => $attendance->id,
                'type' => 'teacher',
                'user' => $attendance->teacher,
                'attendance_date' => $attendance->attendance_date,
                'status' => $attendance->status,
                'notes' => $attendance->notes,
                'source' => 'manual',
            ]);
        }
        
        foreach ($studentAttendances as $attendance) {
            $allAttendances->push([
                'id' => $attendance->id,
                'type' => 'student',
                'user' => $attendance->student,
                'attendance_date' => $attendance->attendance_date,
                'status' => $attendance->status,
                'notes' => $attendance->notes,
                'source' => 'manual',
            ]);
        }
        
        foreach ($locationAttendances as $attendance) {
            // تحديد نوع المستخدم (طالب أو معلم)
            $userType = 'student';
            if ($attendance->user->hasRole('Teacher')) {
                $userType = 'teacher';
            }
            
            $allAttendances->push([
                'id' => $attendance->id,
                'type' => $userType,
                'user' => $attendance->user,
                'attendance_date' => $attendance->attendance_date,
                'status' => $attendance->status === 'outside_range' ? 'absent' : 'present',
                'notes' => 'تسجيل مكاني: ' . $attendance->locationSetting->name . ' - ' . ($attendance->is_within_range ? 'داخل النطاق' : 'خارج النطاق') . ' (' . $attendance->distance_meters . ' متر)',
                'source' => 'location',
            ]);
        }
        
        // ترتيب النتائج حسب التاريخ
        $allAttendances = $allAttendances->sortByDesc('attendance_date');
        
        // إعداد البيانات للعرض
        $attendances = new \Illuminate\Pagination\LengthAwarePaginator(
            $allAttendances->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), 10),
            $allAttendances->count(),
            10,
            \Illuminate\Pagination\Paginator::resolveCurrentPage(),
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
        
        return view('admin.attendance.index', compact('attendances', 'teachers', 'students', 'date', 'userType'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $teachers = User::whereHas('roles', function($query) {
            $query->where('name', 'Teacher');
        })->get();
        
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'Student');
        })->get();
        
        return view('admin.attendance.create', compact('teachers', 'students'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_type' => 'required|in:teacher,student',
            'user_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,absent',
            'notes' => 'nullable|string|max:500',
        ]);
        
        if ($request->user_type == 'teacher') {
            // التحقق من عدم وجود سجل حضور لنفس المعلم في نفس اليوم
            $exists = TeacherAttendance::where('teacher_id', $request->user_id)
                                    ->whereDate('attendance_date', $request->attendance_date)
                                    ->exists();
            
            if ($exists) {
                return redirect()->route('admin.attendance')
                            ->with('error', 'يوجد بالفعل سجل حضور لهذا المعلم في هذا اليوم');
            }
            
            // إنشاء سجل حضور جديد للمعلم
            $attendance = TeacherAttendance::create([
                'teacher_id' => $request->user_id,
                'attendance_date' => $request->attendance_date,
                'status' => $request->status,
                'notes' => $request->notes,
                'recorded_by' => auth()->id(),
            ]);
        } else {
            // التحقق من عدم وجود سجل حضور لنفس الطالب في نفس اليوم
            $exists = StudentAttendance::where('student_id', $request->user_id)
                                    ->whereDate('attendance_date', $request->attendance_date)
                                    ->exists();
            
            if ($exists) {
                return redirect()->route('admin.attendance')
                            ->with('error', 'يوجد بالفعل سجل حضور لهذا الطالب في هذا اليوم');
            }
            
            // إنشاء سجل حضور جديد للطالب
            $attendance = StudentAttendance::create([
                'student_id' => $request->user_id,
                'attendance_date' => $request->attendance_date,
                'status' => $request->status,
                'notes' => $request->notes,
                'recorded_by' => auth()->id(),
            ]);
        }
        
        return redirect()->route('admin.attendance')
                        ->with('success', 'تم تسجيل الحضور بنجاح');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $attendance = TeacherAttendance::with('teacher')->findOrFail($id);
        $teachers = User::whereHas('roles', function($query) {
            $query->where('name', 'Teacher');
        })->get();
        
        return view('admin.attendance.edit', compact('attendance', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,absent',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $attendance = TeacherAttendance::findOrFail($id);
        
        // التحقق من عدم وجود سجل حضور لنفس المعلم في نفس اليوم (إذا تم تغيير المعلم أو التاريخ)
        if ($attendance->teacher_id != $request->teacher_id || $attendance->attendance_date->format('Y-m-d') != $request->attendance_date) {
            $exists = TeacherAttendance::where('teacher_id', $request->teacher_id)
                                  ->whereDate('attendance_date', $request->attendance_date)
                                  ->where('id', '!=', $id)
                                  ->exists();
            
            if ($exists) {
                return redirect()->route('admin.attendance.edit', $id)
                             ->with('error', 'يوجد بالفعل سجل حضور لهذا المعلم في هذا اليوم');
            }
        }
        
        // تحديث سجل الحضور
        $attendance->teacher_id = $request->teacher_id;
        $attendance->attendance_date = $request->attendance_date;
        $attendance->status = $request->status;
        $attendance->notes = $request->notes;
        $attendance->save();
        
        return redirect()->route('admin.attendance')
                         ->with('success', 'تم تحديث سجل الحضور بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
