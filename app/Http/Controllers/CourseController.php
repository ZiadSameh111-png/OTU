<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Admin')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            $courses = Course::with(['teacher', 'groups'])->get();
            return view('admin.courses.index', compact('courses'));
        } elseif ($user->hasRole('Teacher')) {
            $courses = $user->teacherCourses()->with('groups')->get();
            return view('teacher.courses.index', compact('courses'));
        } elseif ($user->hasRole('Student')) {
            return redirect()->route('courses.student');
        }

        return redirect()->route('dashboard')->with('error', 'غير مصرح لك بعرض المقررات.');
    }

    /**
     * Display courses for admin.
     */
    public function adminCourses()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Admin')) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بعرض هذه الصفحة.');
        }
        
        $courses = Course::with(['teacher', 'groups'])->get();
        
        return view('admin.courses.index', compact('courses'));
    }

    /**
     * Display courses for a teacher.
     */
    public function teacherCourses()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Teacher')) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بعرض هذه الصفحة.');
        }
        
        $courses = $user->teacherCourses()->with('groups')->get();
        
        return view('teacher.courses.index', compact('courses'));
    }

    /**
     * Display courses for a student.
     */
    public function studentCourses()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Student')) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بعرض هذه الصفحة.');
        }
        
        if (!$user->group) {
            return view('student.courses.index', ['courses' => collect()]);
        }
        
        $courses = $user->studentCourses()->get();
        
        return view('student.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // الحصول على المعلمين (المستخدمين بدور Teacher)
        $teachers = User::whereHas('roles', function($q) {
            $q->where('name', 'Teacher');
        })->get();
        
        // الحصول على المجموعات النشطة
        $groups = Group::where('active', true)->get();
        
        return view('admin.courses.create', compact('teachers', 'groups'));
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
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:courses',
            'description' => 'nullable|string',
            'teacher_id' => 'nullable|exists:users,id',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:groups,id',
        ]);

        $course = Course::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'teacher_id' => $request->teacher_id,
        ]);

        // ربط المقرر بالمجموعات المحددة
        if ($request->has('groups')) {
            $course->groups()->attach($request->groups);
        }

        return redirect()->route('admin.courses')
            ->with('success', 'تم إضافة المقرر بنجاح.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function show(Course $course)
    {
        $course->load(['teacher', 'groups']);
        
        $user = Auth::user();
        
        if ($user->hasRole('Admin')) {
            return view('admin.courses.show', compact('course'));
        } elseif ($user->hasRole('Teacher') && $course->teacher_id == $user->id) {
            return view('teacher.courses.show', compact('course'));
        } elseif ($user->hasRole('Student') && $user->group && $course->groups->contains($user->group)) {
            return view('student.courses.show', compact('course'));
        }
        
        return redirect()->route('dashboard')->with('error', 'غير مصرح لك بعرض هذا المقرر.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function edit(Course $course)
    {
        // الحصول على المعلمين (المستخدمين بدور Teacher)
        $teachers = User::whereHas('roles', function($q) {
            $q->where('name', 'Teacher');
        })->get();
        
        // الحصول على المجموعات النشطة
        $groups = Group::where('active', true)->get();
        
        // الحصول على معرفات المجموعات المرتبطة بالمقرر
        $courseGroupIds = $course->groups->pluck('id')->toArray();
        
        return view('admin.courses.edit', compact('course', 'teachers', 'groups', 'courseGroupIds'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:courses,code,' . $course->id,
            'description' => 'nullable|string',
            'teacher_id' => 'nullable|exists:users,id',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:groups,id',
        ]);

        $course->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'teacher_id' => $request->teacher_id,
        ]);

        // تحديث العلاقات مع المجموعات
        if ($request->has('groups')) {
            $course->groups()->sync($request->groups);
        } else {
            $course->groups()->detach();
        }

        return redirect()->route('admin.courses')
            ->with('success', 'تم تحديث المقرر بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
    {
        try {
            // فصل المقرر عن المجموعات قبل الحذف
            $course->groups()->detach();
            
            $course->delete();
            return redirect()->route('courses.index')
                ->with('success', 'تم حذف المقرر بنجاح.');
        } catch (\Exception $e) {
            return redirect()->route('courses.index')
                ->with('error', 'لا يمكن حذف المقرر لأنه مرتبط بجداول دراسية.');
        }
    }

    /**
     * Display the specified course for admin.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function adminShow(Course $course)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Admin')) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بعرض هذه الصفحة.');
        }
        
        $course->load(['teacher', 'groups']);
        
        return view('admin.courses.show', compact('course'));
    }
}
