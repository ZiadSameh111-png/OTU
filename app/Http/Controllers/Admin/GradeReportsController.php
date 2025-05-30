<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Student;
use App\Models\User;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use TCPDF;

class GradeReportsController extends Controller
{
    /**
     * Display a listing of the reports.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $courses = Course::all();
        $groups = Group::all();
        $semesters = Course::distinct('semester')->pluck('semester')->filter();
        
        $courseFilter = $request->input('course');
        $groupFilter = $request->input('group');
        $semesterFilter = $request->input('semester');
        
        $gradesQuery = Grade::with(['student', 'course']);
        
        if ($courseFilter) {
            $gradesQuery->where('course_id', $courseFilter);
        }
        
        if ($groupFilter) {
            $gradesQuery->whereHas('student', function ($query) use ($groupFilter) {
                $query->where('group_id', $groupFilter);
            });
        }
        
        if ($semesterFilter) {
            $gradesQuery->whereHas('course', function ($query) use ($semesterFilter) {
                $query->where('semester', $semesterFilter);
            });
        }
        
        $grades = $gradesQuery->get();
        
        // Calculate statistics
        $totalStudents = Student::count();
        $totalCourses = Course::count();
        
        $passCount = $grades->where('score', '>=', 60)->count();
        $failCount = $grades->where('score', '<', 60)->count();
        
        $passRate = $grades->count() > 0 ? ($passCount / $grades->count()) * 100 : 0;
        
        $averageGPA = $grades->avg('gpa') ?? 0;
        
        $gradeDistribution = [
            'A+' => $grades->where('grade', 'A+')->count(),
            'A' => $grades->where('grade', 'A')->count(),
            'A-' => $grades->where('grade', 'A-')->count(),
            'B+' => $grades->where('grade', 'B+')->count(),
            'B' => $grades->where('grade', 'B')->count(),
            'B-' => $grades->where('grade', 'B-')->count(),
            'C+' => $grades->where('grade', 'C+')->count(),
            'C' => $grades->where('grade', 'C')->count(),
            'C-' => $grades->where('grade', 'C-')->count(),
            'D+' => $grades->where('grade', 'D+')->count(),
            'D' => $grades->where('grade', 'D')->count(),
            'F' => $grades->where('grade', 'F')->count(),
        ];
        
        // Get course performance data for the table
        $coursePerformance = Course::withCount(['grades' => function ($query) {
                $query->where('score', '>=', 60);
            }])
            ->withCount('grades as total_students')
            ->withAvg('grades as avg_score', 'score')
            ->get()
            ->map(function ($course) {
                $course->pass_rate = $course->total_students > 0 
                    ? ($course->grades_count / $course->total_students) * 100 
                    : 0;
                return $course;
            });
            
        // Get top performers (students with highest GPA)
        $topPerformers = Student::with('user')
            ->withAvg('grades as gpa', 'gpa')
            ->orderByDesc('gpa')
            ->limit(10)
            ->get();
            
        // Get at-risk students (students with low GPA)
        $atRiskStudents = Student::with('user')
            ->withAvg('grades as gpa', 'gpa')
            ->having('gpa', '<', 2.0)
            ->orderBy('gpa')
            ->limit(10)
            ->get();
        
        // Transform atRiskStudents into lowPerformers format
        $lowPerformers = $atRiskStudents->map(function($student) {
            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'group' => optional($student->group)->name ?? 'غير محدد',
                'gpa' => $student->gpa,
            ];
        });
        
        $stats = [
            'total_students' => $totalStudents,
            'total_courses' => $totalCourses,
            'pass_count' => $passCount,
            'fail_count' => $failCount,
            'pass_rate' => round($passRate, 1),
            'avg_gpa' => round($averageGPA, 2),
            'at_risk_students' => $atRiskStudents->count(),
        ];
        
        return view('admin.grades.reports', compact(
            'courses', 
            'groups', 
            'semesters',
            'grades',
            'stats',
            'gradeDistribution',
            'coursePerformance',
            'topPerformers',
            'atRiskStudents',
            'lowPerformers'
        ));
    }

    /**
     * Display a detailed report for a specific course.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function courseReport($id)
    {
        $course = Course::with(['grades.student.user', 'groups'])->findOrFail($id);
        
        // Get all students in the course with their grades
        $students = $course->grades->map(function ($grade) {
            $student = $grade->student;
            
            // Calculate assignment percentage
            $assignmentMax = $grade->course->assignment_grade ?? 0;
            $assignmentScore = $grade->assignment_score ?? 0;
            $assignmentPercentage = $assignmentMax > 0 ? ($assignmentScore / $assignmentMax) * 100 : 0;
            
            // Calculate final percentage
            $finalMax = $grade->course->final_grade ?? 0;
            $finalScore = $grade->final_score ?? 0;
            $finalPercentage = $finalMax > 0 ? ($finalScore / $finalMax) * 100 : 0;
            
            // Calculate total percentage
            $totalMax = $grade->course->total_grade ?? 100;
            $totalScore = $grade->score ?? 0;
            $totalPercentage = ($totalScore / $totalMax) * 100;
            
            return [
                'id' => $student->user->id,
                'grade_id' => $grade->id,
                'name' => $student->user->name,
                'group' => $student->group ? $student->group->name : 'غير محدد',
                'assignment_score' => $assignmentScore,
                'assignment_max' => $assignmentMax,
                'assignment_percentage' => $assignmentPercentage,
                'final_score' => $finalScore,
                'final_max' => $finalMax,
                'final_percentage' => $finalPercentage,
                'total_score' => $totalScore,
                'total_max' => $totalMax,
                'total_percentage' => $totalPercentage,
                'grade' => $grade->grade,
                'passed' => $grade->score >= 60,
            ];
        });
        
        // Calculate statistics
        $passCount = $course->grades->where('score', '>=', 60)->count();
        $failCount = $course->grades->where('score', '<', 60)->count();
        
        $passRate = $course->grades->count() > 0 ? ($passCount / $course->grades->count()) * 100 : 0;
        
        $avgScore = $course->grades->avg('score') ?? 0;
        $avgMidterm = $course->grades->avg('midterm_score') ?? 0;
        $avgAssignment = $course->grades->avg('assignment_score') ?? 0;
        $avgFinal = $course->grades->avg('final_score') ?? 0;
        
        $atRiskCount = $course->grades->where('score', '<', 50)->count();
        
        $gradeDistribution = [
            'A+' => $course->grades->where('grade', 'A+')->count(),
            'A' => $course->grades->where('grade', 'A')->count(),
            'A-' => $course->grades->where('grade', 'A-')->count(),
            'B+' => $course->grades->where('grade', 'B+')->count(),
            'B' => $course->grades->where('grade', 'B')->count(),
            'B-' => $course->grades->where('grade', 'B-')->count(),
            'C+' => $course->grades->where('grade', 'C+')->count(),
            'C' => $course->grades->where('grade', 'C')->count(),
            'C-' => $course->grades->where('grade', 'C-')->count(),
            'D+' => $course->grades->where('grade', 'D+')->count(),
            'D' => $course->grades->where('grade', 'D')->count(),
            'F' => $course->grades->where('grade', 'F')->count(),
        ];
        
        $stats = [
            'total_students' => $course->grades->count(),
            'pass_count' => $passCount,
            'fail_count' => $failCount,
            'pass_rate' => round($passRate, 1),
            'avg_score' => round($avgScore, 1),
            'avg_midterm' => round($avgMidterm, 1),
            'avg_assignment' => round($avgAssignment, 1),
            'avg_final' => round($avgFinal, 1),
            'at_risk_count' => $atRiskCount,
        ];
        
        return view('admin.grades.course-report', compact('course', 'students', 'stats', 'gradeDistribution'));
    }

    /**
     * Display a detailed report for a specific student.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function studentReport($id)
    {
        $student = Student::with(['user', 'group', 'grades.course.teachers'])->findOrFail($id);
        
        // Calculate GPA
        $gpa = $student->grades->avg('gpa') ?? 0;
        
        // Get current semester courses
        $currentSemester = Carbon::now()->year . '-' . (Carbon::now()->month <= 6 ? '1' : '2');
        
        $currentCourses = $student->grades->filter(function ($grade) use ($currentSemester) {
            return optional($grade->course)->semester == $currentSemester;
        })->map(function ($grade) {
            $course = $grade->course;
            
            // Calculate assignment percentage
            $assignmentMax = $course->assignment_grade ?? 0;
            $assignmentScore = $grade->assignment_score ?? 0;
            $assignmentPercentage = $assignmentMax > 0 ? ($assignmentScore / $assignmentMax) * 100 : 0;
            
            // Calculate final percentage
            $finalMax = $course->final_grade ?? 0;
            $finalScore = $grade->final_score ?? 0;
            $finalPercentage = $finalMax > 0 ? ($finalScore / $finalMax) * 100 : 0;
            
            // Calculate total percentage
            $totalMax = $course->total_grade ?? 100;
            $totalScore = $grade->score ?? 0;
            $totalPercentage = ($totalScore / $totalMax) * 100;
            
            return [
                'name' => $course->name,
                'code' => $course->code,
                'type' => $course->type ?? 'core',
                'credits' => $course->credits ?? 3,
                'instructor' => $course->teachers->count() > 0 ? $course->teachers->pluck('name')->implode('، ') : 'غير محدد',
                'assignment_score' => $assignmentScore,
                'assignment_max' => $assignmentMax,
                'assignment_percentage' => $assignmentPercentage,
                'final_score' => $finalScore,
                'final_max' => $finalMax,
                'final_percentage' => $finalPercentage,
                'total_score' => $totalScore,
                'total_max' => $totalMax,
                'total_percentage' => $totalPercentage,
                'grade' => $grade->grade,
                'status' => $grade->score >= 60 ? 'passed' : 'failed',
            ];
        });
        
        // Get academic history by semester
        $academicHistory = [];
        $semesterGPA = [];
        $semesterCredits = [];
        
        foreach ($student->grades as $grade) {
            if (!$grade->course) {
                continue;
            }
            
            $semester = $grade->course->semester ?? 'غير محدد';
            
            if ($semester == $currentSemester) {
                continue; // Skip current semester as it's shown separately
            }
            
            if (!isset($academicHistory[$semester])) {
                $academicHistory[$semester] = [];
                $semesterGPA[$semester] = 0;
                $semesterCredits[$semester] = 0;
            }
            
            // Add course to semester history
            $academicHistory[$semester][] = [
                'name' => $grade->course->name,
                'code' => $grade->course->code,
                'type' => $grade->course->type ?? 'core',
                'credits' => $grade->course->credits ?? 3,
                'instructor' => $grade->course->teachers->count() > 0 ? $grade->course->teachers->pluck('name')->implode('، ') : 'غير محدد',
                'total_score' => $grade->score,
                'total_max' => $grade->course->total_grade ?? 100,
                'total_percentage' => ($grade->score / ($grade->course->total_grade ?? 100)) * 100,
                'grade' => $grade->grade,
            ];
            
            // Update semester GPA and credits
            $semesterGPA[$semester] += $grade->gpa * ($grade->course->credits ?? 3);
            $semesterCredits[$semester] += $grade->course->credits ?? 3;
        }
        
        // Calculate average GPA per semester
        foreach ($semesterGPA as $semester => $totalGPA) {
            if ($semesterCredits[$semester] > 0) {
                $semesterGPA[$semester] = $totalGPA / $semesterCredits[$semester];
            } else {
                $semesterGPA[$semester] = 0;
            }
        }
        
        // Sort academic history by semester (newest first)
        krsort($academicHistory);
        krsort($semesterGPA);
        krsort($semesterCredits);
        
        // Calculate grade distribution
        $gradeDistribution = [
            'A+' => $student->grades->where('grade', 'A+')->count(),
            'A' => $student->grades->where('grade', 'A')->count(),
            'A-' => $student->grades->where('grade', 'A-')->count(),
            'B+' => $student->grades->where('grade', 'B+')->count(),
            'B' => $student->grades->where('grade', 'B')->count(),
            'B-' => $student->grades->where('grade', 'B-')->count(),
            'C+' => $student->grades->where('grade', 'C+')->count(),
            'C' => $student->grades->where('grade', 'C')->count(),
            'C-' => $student->grades->where('grade', 'C-')->count(),
            'D+' => $student->grades->where('grade', 'D+')->count(),
            'D' => $student->grades->where('grade', 'D')->count(),
            'F' => $student->grades->where('grade', 'F')->count(),
        ];
        
        // Student dummy notes (for demonstration purposes)
        $notes = [
            [
                'id' => 1,
                'title' => 'ملاحظة بشأن الأداء الأكاديمي',
                'content' => 'الطالب يظهر تحسناً ملحوظاً في الفصل الحالي. يُنصح بمتابعة التحسن والاستمرار في الدراسة بنفس المستوى.',
                'date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'author' => 'د. محمد أحمد'
            ],
            [
                'id' => 2,
                'title' => 'ملاحظة حضور',
                'content' => 'الطالب لديه بعض المشكلات في الحضور المنتظم للمحاضرات. يرجى متابعة الحضور بشكل أكثر انتظاماً.',
                'date' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'author' => 'د. سارة خالد'
            ],
        ];
        
        // Calculate statistics
        $totalCredits = $student->grades->sum(function ($grade) {
            return $grade->course ? ($grade->course->credits ?? 3) : 0;
        });
        
        $earnedCredits = $student->grades->where('score', '>=', 60)->sum(function ($grade) {
            return $grade->course ? ($grade->course->credits ?? 3) : 0;
        });
        
        $passedCourses = $student->grades->where('score', '>=', 60)->count();
        $failedCourses = $student->grades->where('score', '<', 60)->count();
        
        $currentSemesterGPA = 0;
        $currentSemesterGrades = $student->grades->filter(function ($grade) use ($currentSemester) {
            return optional($grade->course)->semester == $currentSemester;
        });
        
        if ($currentSemesterGrades->count() > 0) {
            $currentSemesterGPA = $currentSemesterGrades->avg('gpa');
        }
        
        // Dummy statistics for demo purposes
        $stats = [
            'gpa' => $gpa,
            'earned_credits' => $earnedCredits,
            'registered_credits' => $totalCredits,
            'passed_courses' => $passedCourses,
            'failed_courses' => $failedCourses,
            'current_courses' => $currentCourses->count(),
            'attendance_rate' => rand(75, 98), // Dummy attendance rate
            'current_semester_gpa' => $currentSemesterGPA,
            'overall_score' => $student->grades->avg('score') ?? 0,
            'success_rate' => $student->grades->count() > 0 
                ? ($passedCourses / $student->grades->count()) * 100 
                : 0,
            'class_rank' => rand(1, 30), // Dummy class rank
            'class_size' => rand(100, 200), // Dummy class size
        ];
        
        return view('admin.grades.student-report', compact(
            'student',
            'currentSemester',
            'currentCourses',
            'academicHistory',
            'semesterGPA',
            'semesterCredits',
            'gradeDistribution', 
            'notes',
            'stats'
        ));
    }

    /**
     * Export course report in specified format.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function exportCourseReport($id)
    {
        $course = Course::with(['grades.student.user'])->findOrFail($id);
        // Add export logic here
        return response()->json(['message' => 'Export functionality to be implemented']);
    }

    /**
     * Export student report in specified format.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function exportStudentReport($id)
    {
        $student = Student::with(['grades.course'])->findOrFail($id);
        
        // Get current semester courses
        $currentSemester = 'الفصل الحالي'; // You might want to get this from your configuration
        $currentCourses = [];
        foreach ($student->grades as $grade) {
            if ($grade->course->semester === $currentSemester) {
                $currentCourses[] = [
                    'name' => $grade->course->name,
                    'code' => $grade->course->code,
                    'credits' => $grade->course->credit_hours,
                    'assignment_score' => $grade->assignment_score,
                    'assignment_max' => $grade->course->assignment_max,
                    'final_score' => $grade->final_score,
                    'final_max' => $grade->course->final_max,
                    'total_score' => $grade->total_score,
                    'total_max' => $grade->course->total_max,
                    'grade' => $grade->grade,
                ];
            }
        }

        // Get academic history by semester
        $academicHistory = [];
        $semesterGPA = [];
        $semesterCredits = [];
        $gradeDistribution = [
            'A+' => 0, 'A' => 0, 'A-' => 0,
            'B+' => 0, 'B' => 0, 'B-' => 0,
            'C+' => 0, 'C' => 0, 'C-' => 0,
            'D+' => 0, 'D' => 0, 'F' => 0
        ];

        foreach ($student->grades as $grade) {
            $semester = $grade->course->semester;
            if ($semester !== $currentSemester) {
                $academicHistory[$semester][] = [
                    'name' => $grade->course->name,
                    'code' => $grade->course->code,
                    'credits' => $grade->course->credit_hours,
                    'total_score' => $grade->total_score,
                    'total_max' => $grade->course->total_max,
                    'grade' => $grade->grade,
                ];

                // Calculate semester GPA and credits
                if (!isset($semesterGPA[$semester])) {
                    $semesterGPA[$semester] = 0;
                    $semesterCredits[$semester] = 0;
                }
                $semesterGPA[$semester] += $grade->gpa * $grade->course->credit_hours;
                $semesterCredits[$semester] += $grade->course->credit_hours;

                // Update grade distribution
                if (isset($gradeDistribution[$grade->grade])) {
                    $gradeDistribution[$grade->grade]++;
                }
            }
        }

        // Calculate final semester GPAs
        foreach ($semesterGPA as $semester => $totalPoints) {
            $semesterGPA[$semester] = $semesterCredits[$semester] > 0 
                ? $totalPoints / $semesterCredits[$semester] 
                : 0;
        }

        // Calculate statistics
        $stats = [
            'gpa' => $student->gpa,
            'earned_credits' => $student->earned_credits,
            'registered_credits' => $student->registered_credits,
            'passed_courses' => $student->grades->where('grade', '!=', 'F')->count(),
            'failed_courses' => $student->grades->where('grade', 'F')->count(),
            'current_courses' => count($currentCourses),
            'attendance_rate' => 95, // You might want to calculate this from actual attendance data
        ];

        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('OTU System');
        $pdf->SetAuthor('OTU System');
        $pdf->SetTitle('تقرير الطالب - ' . $student->name);

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, 15);

        // Add Arabic font
        $pdf->AddFont('DejaVuSans', '', 'DejaVuSans.ttf', true);
        $pdf->AddFont('DejaVuSans', 'B', 'DejaVuSans-Bold.ttf', true);

        // Set font
        $pdf->SetFont('DejaVuSans', '', 12, '', true);

        // Add a page
        $pdf->AddPage();

        // Set RTL direction
        $pdf->setRTL(true);

        // Create the HTML content
        $html = view('admin.grades.student-report-pdf', compact(
            'student',
            'currentSemester',
            'currentCourses',
            'academicHistory',
            'semesterGPA',
            'semesterCredits',
            'gradeDistribution',
            'stats'
        ))->render();

        // Write HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        // Close and output PDF document
        return response($pdf->Output('student_report_' . $student->id . '.pdf', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="student_report_' . $student->id . '.pdf"');
    }

    /**
     * Export the general grades report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportReport(Request $request)
    {
        $gradesQuery = Grade::with(['student.user', 'course']);
        
        if ($request->has('course')) {
            $gradesQuery->where('course_id', $request->course);
        }
        
        if ($request->has('group')) {
            $gradesQuery->whereHas('student', function ($query) use ($request) {
                $query->where('group_id', $request->group);
            });
        }
        
        if ($request->has('semester')) {
            $gradesQuery->whereHas('course', function ($query) use ($request) {
                $query->where('semester', $request->semester);
            });
        }
        
        $grades = $gradesQuery->get();
        
        $data = [
            'grades' => $grades,
            'stats' => [
                'total_students' => Student::count(),
                'total_courses' => Course::count(),
                'pass_count' => $grades->where('score', '>=', 60)->count(),
                'fail_count' => $grades->where('score', '<', 60)->count(),
                'avg_gpa' => round($grades->avg('gpa') ?? 0, 2),
            ],
            'filters' => [
                'course' => $request->course ? Course::find($request->course)->name : 'All Courses',
                'group' => $request->group ? Group::find($request->group)->name : 'All Groups',
                'semester' => $request->semester ?? 'All Semesters',
            ],
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
        
        $pdf = \PDF::loadView('admin.grades.reports-pdf', $data);
        
        return $pdf->download('grades-report.pdf');
    }

    /**
     * Delete a student report
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function deleteStudentReport($id)
    {
        $student = Student::findOrFail($id);
        
        try {
            // Delete student grades
            $student->grades()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'تم حذف تقرير الطالب بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف تقرير الطالب'
            ], 500);
        }
    }
} 