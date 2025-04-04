<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherAttendance;
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
        
        // بناء استعلام لسجلات الحضور
        $query = TeacherAttendance::with('teacher');
        
        // تصفية حسب التاريخ إذا تم تحديده
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('attendance_date', $request->date);
        }
        
        // الحصول على سجلات الحضور مع التصنيف حسب التاريخ
        $attendances = $query->orderBy('attendance_date', 'desc')
                         ->paginate(10);
        
        return view('admin.attendance.index', compact('attendances', 'teachers'));
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
        
        return view('admin.attendance.create', compact('teachers'));
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
            'teacher_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,absent',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // التحقق من عدم وجود سجل حضور لنفس المعلم في نفس اليوم
        $exists = TeacherAttendance::where('teacher_id', $request->teacher_id)
                                  ->whereDate('attendance_date', $request->attendance_date)
                                  ->exists();
        
        if ($exists) {
            return redirect()->route('admin.attendance')
                         ->with('error', 'يوجد بالفعل سجل حضور لهذا المعلم في هذا اليوم');
        }
        
        // إنشاء سجل حضور جديد
        $attendance = TeacherAttendance::create([
            'teacher_id' => $request->teacher_id,
            'attendance_date' => $request->attendance_date,
            'status' => $request->status,
            'notes' => $request->notes,
            'recorded_by' => auth()->id(),
        ]);
        
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
