<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use App\Models\Exam;
use App\Models\StudentExamAttempt;
use App\Models\StudentExamAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GradeReportController extends Controller
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
     * Display teacher grade reports index.
     *
     * @return \Illuminate\View\Response
     */
    public function teacherReportsIndex(Request $request)
    {
        $teacher = Auth::user();
        if (!$teacher->hasRole('Teacher')) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $courses = Course::where('teacher_id', $teacher->id)->get();
        $selectedCourseId = $request->input('course_id');
        $selectedGroupId = $request->input('group_id');
        
        $query = Grade::with(['student', 'course'])
            ->whereHas('course', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            });
        
        if ($selectedCourseId) {
            $query->where('course_id', $selectedCourseId);
            $selectedCourse = Course::find($selectedCourseId);
            $groups = $selectedCourse ? $selectedCourse->groups : collect();
        } else {
            $groups = Group::whereHas('courses', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })->get();
        }
        
        if ($selectedGroupId) {
            $query->whereHas('student', function($q) use ($selectedGroupId) {
                $q->where('group_id', $selectedGroupId);
            });
        }
        
        $grades = $query->get();
        
        // Estadísticas
        $stats = [
            'total_students' => $grades->count(),
            'avg_online_percentage' => $grades->avg(function($grade) {
                return $grade->getOnlineExamPercentageAttribute() ?? 0;
            }),
            'avg_paper_percentage' => $grades->avg(function($grade) {
                return $grade->getPaperExamPercentageAttribute() ?? 0;
            }),
            'avg_practical_percentage' => $grades->avg(function($grade) {
                return $grade->getPracticalPercentageAttribute() ?? 0;
            }),
            'avg_total_percentage' => $grades->avg(function($grade) {
                return $grade->getTotalPercentageAttribute() ?? 0;
            }),
            'finalized_count' => $grades->where('is_final', true)->count(),
        ];
        
        return view('teacher.grades.reports', compact('courses', 'groups', 'grades', 'stats', 'selectedCourseId', 'selectedGroupId'));
    }

    /**
     * Display detailed student grades for teacher.
     *
     * @param int $studentId
     * @param int $courseId
     * @return \Illuminate\View\Response
     */
    public function teacherStudentDetail($studentId, $courseId)
    {
        $teacher = Auth::user();
        if (!$teacher->hasRole('Teacher')) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }
        
        $course = Course::findOrFail($courseId);
        
        // Verificar que el profesor es el responsable del curso
        if ($course->teacher_id !== $teacher->id) {
            return redirect()->route('teacher.grades.reports')->with('error', 'غير مصرح لك بعرض هذه البيانات');
        }
        
        $student = User::findOrFail($studentId);
        
        // Obtener la calificación
        $grade = Grade::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->first();
            
        if (!$grade) {
            // Si no existe, crear un registro vacío para poder mostrar el formulario
            $grade = new Grade([
                'student_id' => $studentId,
                'course_id' => $courseId,
            ]);
            $grade->save();
            
            // Actualizar las calificaciones de exámenes electrónicos
            $grade->updateOnlineExamGrades();
        }
        
        // Obtener los intentos de exámenes para este estudiante en este curso
        $attempts = StudentExamAttempt::whereHas('exam', function($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->where('student_id', $studentId)
            ->where('status', 'graded')
            ->with(['exam', 'answers.question'])
            ->get();
        
        return view('teacher.grades.student-detail', compact('student', 'course', 'grade', 'attempts'));
    }

    /**
     * Update grades for a specific student in a course.
     *
     * @param Request $request
     * @param int $studentId
     * @param int $courseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStudentGrades(Request $request, $studentId, $courseId)
    {
        $teacher = Auth::user();
        if (!$teacher->hasRole('Teacher')) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }
        
        $course = Course::findOrFail($courseId);
        
        // Verificar que el profesor es el responsable del curso
        if ($course->teacher_id !== $teacher->id) {
            return redirect()->route('teacher.grades.reports')->with('error', 'غير مصرح لك بتعديل هذه البيانات');
        }
        
        // Validar los datos de entrada
        $request->validate([
            'online_exam_grade' => 'nullable|numeric|min:0',
            'online_exam_total' => 'nullable|numeric|min:0',
            'paper_exam_grade' => 'nullable|numeric|min:0',
            'paper_exam_total' => 'nullable|numeric|min:0',
            'practical_grade' => 'nullable|numeric|min:0',
            'practical_total' => 'nullable|numeric|min:0',
            'comments' => 'nullable|string',
            'is_final' => 'boolean',
        ]);
        
        $grade = Grade::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->first();
            
        if (!$grade) {
            $grade = new Grade();
            $grade->student_id = $studentId;
            $grade->course_id = $courseId;
        }
        
        // Actualizar calificaciones
        $grade->online_exam_grade = $request->input('online_exam_grade');
        $grade->online_exam_total = $request->input('online_exam_total');
        $grade->paper_exam_grade = $request->input('paper_exam_grade');
        $grade->paper_exam_total = $request->input('paper_exam_total');
        $grade->practical_grade = $request->input('practical_grade');
        $grade->practical_total = $request->input('practical_total');
        $grade->comments = $request->input('comments');
        $grade->is_final = $request->has('is_final');
        $grade->updated_by = $teacher->id;
        
        // Calcular total
        $grade->calculateTotalGrade();
        
        return redirect()->route('teacher.grades.student-detail', ['studentId' => $studentId, 'courseId' => $courseId])
            ->with('success', 'تم تحديث الدرجات بنجاح');
    }

    /**
     * Display admin grade reports index.
     *
     * @return \Illuminate\View\Response
     */
    public function adminReportsIndex(Request $request)
    {
        $admin = Auth::user();
        if (!$admin->hasRole('Admin')) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $courses = Course::with('teacher')->get();
        $groups = Group::all();
        
        $selectedCourseId = $request->input('course_id');
        $selectedGroupId = $request->input('group_id');
        
        $query = Grade::with(['student', 'course', 'course.teacher'])
            ->when($selectedCourseId, function($q) use ($selectedCourseId) {
                return $q->where('course_id', $selectedCourseId);
            })
            ->when($selectedGroupId, function($q) use ($selectedGroupId) {
                return $q->whereHas('student', function($sq) use ($selectedGroupId) {
                    $sq->where('group_id', $selectedGroupId);
                });
            });
        
        $grades = $query->get();
        
        // Estadísticas
        $stats = [
            'total_students' => User::whereHas('roles', function($q) {
                $q->where('name', 'Student');
            })->count(),
            'total_courses' => Course::count(),
            'total_grades' => Grade::count(),
            'finalized_grades' => Grade::where('is_final', true)->count(),
            'avg_total_percentage' => Grade::where('is_final', true)->whereNotNull('total_grade')->avg(DB::raw('(total_grade / total_possible) * 100')),
        ];
        
        return view('admin.grades.reports', compact('courses', 'groups', 'grades', 'stats', 'selectedCourseId', 'selectedGroupId'));
    }

    /**
     * Export grades report to Excel/PDF
     *
     * @param Request $request
     * @param string $format
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportGrades(Request $request, $format = 'excel')
    {
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->hasRole('Teacher')) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $courseId = $request->input('course_id');
        $groupId = $request->input('group_id');
        
        $query = Grade::with(['student', 'course', 'course.teacher']);
        
        // Si es profesor, solo puede exportar sus cursos
        if ($user->hasRole('Teacher')) {
            $query->whereHas('course', function($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }
        
        if ($courseId) {
            $query->where('course_id', $courseId);
        }
        
        if ($groupId) {
            $query->whereHas('student', function($q) use ($groupId) {
                $q->where('group_id', $groupId);
            });
        }
        
        $grades = $query->get();
        
        // Preparar datos para exportación
        $exportData = [];
        foreach ($grades as $grade) {
            $exportData[] = [
                'student_id' => $grade->student->id,
                'student_name' => $grade->student->name,
                'course' => $grade->course->name,
                'teacher' => $grade->course->teacher->name,
                'online_exam_grade' => $grade->online_exam_grade,
                'online_exam_total' => $grade->online_exam_total,
                'paper_exam_grade' => $grade->paper_exam_grade,
                'paper_exam_total' => $grade->paper_exam_total,
                'practical_grade' => $grade->practical_grade,
                'practical_total' => $grade->practical_total,
                'total_grade' => $grade->total_grade,
                'total_possible' => $grade->total_possible,
                'percentage' => $grade->getTotalPercentageAttribute() ? round($grade->getTotalPercentageAttribute(), 1) . '%' : 'N/A',
                'is_final' => $grade->is_final ? 'نعم' : 'لا',
            ];
        }
        
        // Para esta implementación simple, solo devuelve un JSON
        // En una implementación real, usaría una biblioteca como Laravel Excel o DOMPDF
        if ($format === 'excel') {
            return response()->json($exportData);
        } elseif ($format === 'pdf') {
            return response()->json($exportData);
        }
        
        return redirect()->back()->with('error', 'صيغة التصدير غير صالحة');
    }

    /**
     * Display student grade report.
     *
     * @return \Illuminate\View\Response
     */
    public function studentReport(Request $request)
    {
        $student = Auth::user();
        if (!$student->hasRole('Student')) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $courseId = $request->input('course_id');
        
        $query = Grade::with(['course', 'course.teacher'])
            ->where('student_id', $student->id);
            
        if ($courseId) {
            $query->where('course_id', $courseId);
        }
        
        $grades = $query->get();
        
        // Obtener todos los cursos del estudiante
        $courses = Course::whereHas('groups', function($q) use ($student) {
            $q->where('groups.id', $student->group_id);
        })->get();
        
        return view('student.grades.report', compact('grades', 'courses', 'courseId'));
    }

    /**
     * Display student grade details for a specific course.
     *
     * @param int $courseId
     * @return \Illuminate\View\Response
     */
    public function studentCourseDetail($courseId)
    {
        $student = Auth::user();
        if (!$student->hasRole('Student')) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }
        
        $course = Course::findOrFail($courseId);
        
        // Verificar que el estudiante está en un grupo que tiene acceso a este curso
        $hasAccess = $course->groups()->where('groups.id', $student->group_id)->exists();
        if (!$hasAccess) {
            return redirect()->route('student.grades.report')->with('error', 'غير مصرح لك بعرض هذه البيانات');
        }
        
        // Obtener la calificación
        $grade = Grade::where('student_id', $student->id)
            ->where('course_id', $courseId)
            ->first();
            
        if (!$grade) {
            return redirect()->route('student.grades.report')->with('error', 'لم يتم تسجيل درجات لهذا المقرر بعد');
        }
        
        // Obtener los intentos de exámenes para este estudiante en este curso
        $attempts = StudentExamAttempt::whereHas('exam', function($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->where('student_id', $student->id)
            ->where('status', 'graded')
            ->with(['exam', 'answers.question'])
            ->get();
        
        return view('student.grades.course-detail', compact('course', 'grade', 'attempts'));
    }

    /**
     * View exam attempt details.
     *
     * @param int $attemptId
     * @return \Illuminate\View\Response
     */
    public function viewExamDetail($attemptId)
    {
        $user = Auth::user();
        
        $attempt = StudentExamAttempt::with(['exam', 'student', 'answers.question'])
            ->findOrFail($attemptId);
        
        // Verificar acceso
        if ($user->hasRole('Student')) {
            // Los estudiantes solo pueden ver sus propios intentos
            if ($attempt->student_id !== $user->id) {
                return redirect()->route('dashboard')->with('error', 'غير مصرح لك بعرض هذه البيانات');
            }
        } elseif ($user->hasRole('Teacher')) {
            // Los profesores solo pueden ver intentos de sus cursos
            if ($attempt->exam->teacher_id !== $user->id) {
                return redirect()->route('dashboard')->with('error', 'غير مصرح لك بعرض هذه البيانات');
            }
        } elseif (!$user->hasRole('Admin')) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بعرض هذه البيانات');
        }
        
        return view('grades.exam-detail', compact('attempt'));
    }

    /**
     * Update online exam grades for all students.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateAllOnlineGrades()
    {
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->hasRole('Teacher')) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بتنفيذ هذه العملية');
        }

        $query = Grade::query();
        
        // Si es profesor, solo puede actualizar sus cursos
        if ($user->hasRole('Teacher')) {
            $query->whereHas('course', function($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }
        
        $grades = $query->get();
        
        $updatedCount = 0;
        foreach ($grades as $grade) {
            $grade->updateOnlineExamGrades();
            $updatedCount++;
        }
        
        return redirect()->back()->with('success', "تم تحديث $updatedCount درجة اختبار إلكتروني بنجاح");
    }
}
