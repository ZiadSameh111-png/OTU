<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Course;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
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
     * Display teacher's course list for grade management.
     *
     * @return \Illuminate\Http\Response
     */
    public function teacherIndex()
    {
        $teacher = Auth::user();
        if ($teacher->role !== 'Teacher') {
            return redirect()->route('home')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $courses = $teacher->teacherCourses;
        
        return view('teacher.grades.index', compact('courses'));
    }

    /**
     * Display the grade management page for a specific course.
     *
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function manageCourse($courseId)
    {
        $teacher = Auth::user();
        if ($teacher->role !== 'Teacher') {
            return redirect()->route('home')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $course = Course::with(['groups.students'])->findOrFail($courseId);
        
        // Check if the teacher is authorized to manage this course
        if ($course->teacher_id !== $teacher->id) {
            return redirect()->route('teacher.grades.index')->with('error', 'غير مصرح لك بإدارة درجات هذا المقرر');
        }

        $groups = $course->groups;
        $grades = Grade::where('course_id', $courseId)->get();
        
        return view('teacher.grades.manage', compact('course', 'groups', 'grades'));
    }

    /**
     * Store grades for students.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $teacher = Auth::user();
        if ($teacher->role !== 'Teacher') {
            return response()->json(['error' => 'غير مصرح لك بالوصول إلى هذه الصفحة'], 403);
        }

        $courseId = $request->input('course_id');
        $course = Course::findOrFail($courseId);
        
        // Check if the teacher is authorized to manage this course
        if ($course->teacher_id !== $teacher->id) {
            return response()->json(['error' => 'غير مصرح لك بإدارة درجات هذا المقرر'], 403);
        }

        $grades = $request->input('grades', []);
        
        DB::beginTransaction();
        try {
            foreach ($grades as $studentId => $gradeData) {
                $assignmentGrade = isset($gradeData['assignment_grade']) ? $gradeData['assignment_grade'] : null;
                $finalGrade = isset($gradeData['final_grade']) ? $gradeData['final_grade'] : null;
                
                // Validate assignment grade
                if ($assignmentGrade !== null && ($assignmentGrade < 0 || $assignmentGrade > $course->assignment_grade)) {
                    throw new \Exception('درجة الأعمال الفصلية غير صالحة للطالب رقم ' . $studentId);
                }
                
                // Validate final grade
                if ($finalGrade !== null && ($finalGrade < 0 || $finalGrade > $course->final_grade)) {
                    throw new \Exception('درجة الاختبار النهائي غير صالحة للطالب رقم ' . $studentId);
                }
                
                Grade::updateOrCreate(
                    [
                        'course_id' => $courseId,
                        'student_id' => $studentId,
                    ],
                    [
                        'assignment_grade' => $assignmentGrade,
                        'final_grade' => $finalGrade,
                        'updated_by' => $teacher->id,
                    ]
                );
            }
            
            DB::commit();
            return response()->json(['success' => 'تم حفظ الدرجات بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Submit grades for students (finalize grades).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function submit(Request $request)
    {
        $teacher = Auth::user();
        if ($teacher->role !== 'Teacher') {
            return response()->json(['error' => 'غير مصرح لك بالوصول إلى هذه الصفحة'], 403);
        }

        $courseId = $request->input('course_id');
        $course = Course::findOrFail($courseId);
        
        // Check if the teacher is authorized to manage this course
        if ($course->teacher_id !== $teacher->id) {
            return response()->json(['error' => 'غير مصرح لك بإدارة درجات هذا المقرر'], 403);
        }

        $grades = $request->input('grades', []);
        
        DB::beginTransaction();
        try {
            foreach ($grades as $studentId => $gradeData) {
                $assignmentGrade = isset($gradeData['assignment_grade']) ? $gradeData['assignment_grade'] : null;
                $finalGrade = isset($gradeData['final_grade']) ? $gradeData['final_grade'] : null;
                
                // Both grades must be provided for submission
                if ($assignmentGrade === null || $finalGrade === null) {
                    throw new \Exception('يجب إدخال جميع الدرجات قبل التأكيد والإرسال');
                }
                
                // Validate assignment grade
                if ($assignmentGrade < 0 || $assignmentGrade > $course->assignment_grade) {
                    throw new \Exception('درجة الأعمال الفصلية غير صالحة للطالب رقم ' . $studentId);
                }
                
                // Validate final grade
                if ($finalGrade < 0 || $finalGrade > $course->final_grade) {
                    throw new \Exception('درجة الاختبار النهائي غير صالحة للطالب رقم ' . $studentId);
                }
                
                Grade::updateOrCreate(
                    [
                        'course_id' => $courseId,
                        'student_id' => $studentId,
                    ],
                    [
                        'assignment_grade' => $assignmentGrade,
                        'final_grade' => $finalGrade,
                        'submitted' => true,
                        'submission_date' => now(),
                        'updated_by' => $teacher->id,
                    ]
                );
            }
            
            DB::commit();
            return response()->json(['success' => 'تم تأكيد وإرسال الدرجات بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Display student's grades.
     *
     * @return \Illuminate\Http\Response
     */
    public function studentIndex()
    {
        $student = Auth::user();
        if ($student->role !== 'Student') {
            return redirect()->route('home')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $group = $student->group;
        $courses = collect([]);
        $grades = collect([]);
        
        if ($group) {
            $courses = $group->courses;
            $studentId = $student->id;
            $grades = Grade::whereIn('course_id', $courses->pluck('id'))
                          ->where('student_id', $studentId)
                          ->get();
        }
        
        return view('student.grades.index', compact('courses', 'grades', 'group'));
    }

    /**
     * Display grades for admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function adminIndex()
    {
        $user = Auth::user();
        if ($user->role !== 'Admin') {
            return redirect()->route('home')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $courses = Course::with('teacher')->get();
        $groups = Group::where('active', true)->get();
        
        // Get submission statistics
        $courseStats = [];
        foreach ($courses as $course) {
            $studentCount = 0;
            $submittedCount = 0;
            
            foreach ($course->groups as $group) {
                $groupStudentCount = $group->students->count();
                $studentCount += $groupStudentCount;
                $submittedCount += Grade::where('course_id', $course->id)
                                       ->whereIn('student_id', $group->students->pluck('id'))
                                       ->where('submitted', true)
                                       ->count();
            }
            
            $courseStats[$course->id] = [
                'total' => $studentCount,
                'submitted' => $submittedCount,
                'percentage' => $studentCount > 0 ? round(($submittedCount / $studentCount) * 100) : 0
            ];
        }
        
        return view('admin.grades.index', compact('courses', 'groups', 'courseStats'));
    }

    /**
     * Display grades for a specific course (admin view).
     *
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function adminViewCourse($courseId)
    {
        $user = Auth::user();
        if ($user->role !== 'Admin') {
            return redirect()->route('home')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $course = Course::with(['groups.students', 'teacher'])->findOrFail($courseId);
        $grades = Grade::where('course_id', $courseId)->get();
        
        return view('admin.grades.view', compact('course', 'grades'));
    }

    /**
     * Display comprehensive grade reports for administration.
     *
     * @return \Illuminate\Http\Response
     */
    public function adminReports()
    {
        $user = Auth::user();
        if ($user->role !== 'Admin') {
            return redirect()->route('home')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $courses = Course::with(['teacher', 'groups', 'grades'])->get();
        $groups = Group::where('active', true)->get();
        
        // Calculate statistics for each course
        $courseStats = [];
        foreach ($courses as $course) {
            $gradesData = [
                'A' => 0,
                'B' => 0,
                'C' => 0,
                'D' => 0,
                'F' => 0,
            ];
            
            $totalSubmitted = 0;
            $totalGrades = 0;
            
            foreach ($course->grades as $grade) {
                if ($grade->submitted) {
                    $totalSubmitted++;
                    $letterGrade = $grade->getLetterGradeAttribute();
                    
                    // Count only the base letter grade (A, B, C, D, F)
                    $baseGrade = substr($letterGrade, 0, 1);
                    if (array_key_exists($baseGrade, $gradesData)) {
                        $gradesData[$baseGrade]++;
                    }
                }
                $totalGrades++;
            }
            
            $courseStats[$course->id] = [
                'submitted' => $totalSubmitted,
                'total' => $totalGrades,
                'grades' => $gradesData,
                'percentage' => $totalGrades > 0 ? round(($totalSubmitted / $totalGrades) * 100) : 0,
            ];
        }
        
        // Calculate statistics for each group
        $groupStats = [];
        foreach ($groups as $group) {
            $students = $group->students;
            $totalCourses = $group->courses->count() * $students->count();
            $submittedGrades = 0;
            
            foreach ($group->courses as $course) {
                foreach ($students as $student) {
                    $grade = Grade::where('course_id', $course->id)
                                  ->where('student_id', $student->id)
                                  ->where('submitted', true)
                                  ->first();
                    
                    if ($grade) {
                        $submittedGrades++;
                    }
                }
            }
            
            $groupStats[$group->id] = [
                'submitted' => $submittedGrades,
                'total' => $totalCourses,
                'percentage' => $totalCourses > 0 ? round(($submittedGrades / $totalCourses) * 100) : 0,
            ];
        }
        
        return view('admin.grades.reports', compact('courses', 'groups', 'courseStats', 'groupStats'));
    }

    /**
     * Display detailed course report for a specific course.
     *
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function courseReport($courseId)
    {
        $user = Auth::user();
        if ($user->role !== 'Admin') {
            return redirect()->route('home')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $course = Course::with(['teacher', 'groups.students', 'grades'])->findOrFail($courseId);
        
        // Get all grades for this course, grouped by grade letter
        $gradeDistribution = [
            'A+' => 0, 'A' => 0, 
            'B+' => 0, 'B' => 0, 
            'C+' => 0, 'C' => 0, 
            'D' => 0, 'F' => 0
        ];
        
        $totalGrades = 0;
        $submittedGrades = 0;
        $passedGrades = 0;
        $failedGrades = 0;
        
        foreach ($course->grades as $grade) {
            $totalGrades++;
            
            if ($grade->submitted) {
                $submittedGrades++;
                $letterGrade = $grade->getLetterGradeAttribute();
                
                if (array_key_exists($letterGrade, $gradeDistribution)) {
                    $gradeDistribution[$letterGrade]++;
                }
                
                // Count passed/failed
                if ($letterGrade != 'F') {
                    $passedGrades++;
                } else {
                    $failedGrades++;
                }
            }
        }
        
        // Calculate statistics for each group in this course
        $groupStats = [];
        foreach ($course->groups as $group) {
            $students = $group->students;
            $totalStudents = $students->count();
            $groupGrades = [
                'submitted' => 0,
                'passed' => 0,
                'failed' => 0,
                'average' => 0,
            ];
            
            $totalScore = 0;
            $scoreCount = 0;
            
            foreach ($students as $student) {
                $grade = Grade::where('course_id', $courseId)
                              ->where('student_id', $student->id)
                              ->first();
                
                if ($grade && $grade->submitted) {
                    $groupGrades['submitted']++;
                    
                    $letterGrade = $grade->getLetterGradeAttribute();
                    if ($letterGrade != 'F') {
                        $groupGrades['passed']++;
                    } else {
                        $groupGrades['failed']++;
                    }
                    
                    // Calculate average
                    $total = $grade->assignment_grade + $grade->final_grade;
                    $totalScore += $total;
                    $scoreCount++;
                }
            }
            
            $groupGrades['average'] = $scoreCount > 0 ? round($totalScore / $scoreCount, 2) : 0;
            $groupGrades['submission_rate'] = $totalStudents > 0 ? round(($groupGrades['submitted'] / $totalStudents) * 100) : 0;
            $groupGrades['pass_rate'] = $groupGrades['submitted'] > 0 ? round(($groupGrades['passed'] / $groupGrades['submitted']) * 100) : 0;
            
            $groupStats[$group->id] = $groupGrades;
        }
        
        $stats = [
            'total' => $totalGrades,
            'submitted' => $submittedGrades,
            'passed' => $passedGrades,
            'failed' => $failedGrades,
            'submission_rate' => $totalGrades > 0 ? round(($submittedGrades / $totalGrades) * 100) : 0,
            'pass_rate' => $submittedGrades > 0 ? round(($passedGrades / $submittedGrades) * 100) : 0,
            'grade_distribution' => $gradeDistribution,
        ];
        
        return view('admin.grades.course_report', compact('course', 'stats', 'groupStats'));
    }

    /**
     * Display detailed group report for a specific group.
     *
     * @param  int  $groupId
     * @return \Illuminate\Http\Response
     */
    public function groupReport($groupId)
    {
        $user = Auth::user();
        if ($user->role !== 'Admin') {
            return redirect()->route('home')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $group = Group::with(['students', 'courses.teacher'])->findOrFail($groupId);
        
        // Calculate statistics for each course for this group
        $courseStats = [];
        foreach ($group->courses as $course) {
            $students = $group->students;
            $totalStudents = $students->count();
            $courseGrades = [
                'submitted' => 0,
                'passed' => 0,
                'failed' => 0,
                'average' => 0,
            ];
            
            $totalScore = 0;
            $scoreCount = 0;
            
            foreach ($students as $student) {
                $grade = Grade::where('course_id', $course->id)
                              ->where('student_id', $student->id)
                              ->first();
                
                if ($grade && $grade->submitted) {
                    $courseGrades['submitted']++;
                    
                    $letterGrade = $grade->getLetterGradeAttribute();
                    if ($letterGrade != 'F') {
                        $courseGrades['passed']++;
                    } else {
                        $courseGrades['failed']++;
                    }
                    
                    // Calculate average
                    $total = $grade->assignment_grade + $grade->final_grade;
                    $totalScore += $total;
                    $scoreCount++;
                }
            }
            
            $courseGrades['average'] = $scoreCount > 0 ? round($totalScore / $scoreCount, 2) : 0;
            $courseGrades['submission_rate'] = $totalStudents > 0 ? round(($courseGrades['submitted'] / $totalStudents) * 100) : 0;
            $courseGrades['pass_rate'] = $courseGrades['submitted'] > 0 ? round(($courseGrades['passed'] / $courseGrades['submitted']) * 100) : 0;
            
            $courseStats[$course->id] = $courseGrades;
        }
        
        // Calculate statistics for each student in this group
        $studentStats = [];
        foreach ($group->students as $student) {
            $coursesCount = $group->courses->count();
            $studentGrades = [
                'submitted' => 0,
                'passed' => 0,
                'failed' => 0,
                'average' => 0,
            ];
            
            $totalScore = 0;
            $scoreCount = 0;
            
            foreach ($group->courses as $course) {
                $grade = Grade::where('course_id', $course->id)
                              ->where('student_id', $student->id)
                              ->first();
                
                if ($grade && $grade->submitted) {
                    $studentGrades['submitted']++;
                    
                    $letterGrade = $grade->getLetterGradeAttribute();
                    if ($letterGrade != 'F') {
                        $studentGrades['passed']++;
                    } else {
                        $studentGrades['failed']++;
                    }
                    
                    // Calculate average
                    $total = $grade->assignment_grade + $grade->final_grade;
                    $totalScore += $total;
                    $scoreCount++;
                }
            }
            
            $studentGrades['average'] = $scoreCount > 0 ? round($totalScore / $scoreCount, 2) : 0;
            $studentGrades['submission_rate'] = $coursesCount > 0 ? round(($studentGrades['submitted'] / $coursesCount) * 100) : 0;
            $studentGrades['pass_rate'] = $studentGrades['submitted'] > 0 ? round(($studentGrades['passed'] / $studentGrades['submitted']) * 100) : 0;
            
            $studentStats[$student->id] = $studentGrades;
        }
        
        return view('admin.grades.group_report', compact('group', 'courseStats', 'studentStats'));
    }

    /**
     * Export course grades in the specified format.
     *
     * @param  int  $courseId
     * @param  string  $format
     * @return \Illuminate\Http\Response
     */
    public function exportCourseGrades($courseId, $format = 'excel')
    {
        $user = Auth::user();
        if ($user->role !== 'Admin') {
            return redirect()->route('home')->with('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $course = Course::with(['groups.students', 'grades'])->findOrFail($courseId);
        
        // Format data for export
        $exportData = [];
        
        foreach ($course->groups as $group) {
            foreach ($group->students as $student) {
                $grade = Grade::where('course_id', $courseId)
                              ->where('student_id', $student->id)
                              ->first();
                
                $row = [
                    'student_id' => $student->student_id ?? $student->id,
                    'student_name' => $student->name,
                    'group' => $group->name,
                    'assignment_grade' => $grade && $grade->submitted ? $grade->assignment_grade : '-',
                    'final_grade' => $grade && $grade->submitted ? $grade->final_grade : '-',
                    'total' => $grade && $grade->submitted ? ($grade->assignment_grade + $grade->final_grade) : '-',
                    'letter_grade' => $grade && $grade->submitted ? $grade->getLetterGradeAttribute() : '-',
                    'submitted' => $grade ? ($grade->submitted ? 'نعم' : 'لا') : 'لا',
                    'submission_date' => $grade && $grade->submission_date ? $grade->submission_date->format('Y-m-d') : '-',
                ];
                
                $exportData[] = $row;
            }
        }
        
        $fileName = 'course_grades_' . $course->code . '_' . date('Ymd');
        
        // Handle different export formats
        if ($format == 'pdf') {
            // Implementation for PDF export would go here
            // This requires a PDF library like dompdf, mpdf, etc.
            return redirect()->back()->with('error', 'تصدير PDF غير متاح حاليًا');
        } elseif ($format == 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '.csv"',
            ];
            
            $callback = function() use ($exportData) {
                $file = fopen('php://output', 'w');
                
                // Add CSV headers with proper encoding for Arabic
                fputcsv($file, [
                    'رقم الطالب',
                    'اسم الطالب',
                    'المجموعة',
                    'درجة الأعمال الفصلية',
                    'درجة الاختبار النهائي',
                    'المجموع',
                    'التقدير',
                    'تم التأكيد',
                    'تاريخ التأكيد'
                ]);
                
                foreach ($exportData as $row) {
                    fputcsv($file, $row);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        } else { // default to excel
            // Implementation for Excel export would go here
            // This requires a library like PhpSpreadsheet or Maatwebsite/Laravel-Excel
            return redirect()->back()->with('error', 'تصدير Excel غير متاح حاليًا. يرجى استخدام تنسيق CSV.');
        }
    }
}
