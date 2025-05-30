<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    /**
     * Display a listing of grades for the authenticated student.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();
        $grades = Grade::with('course')->where('student_id', $student->id)->get();
        
        return view('student.grades.index', compact('grades', 'student'));
    }

    /**
     * Display a detailed report of the student's grades.
     *
     * @return \Illuminate\Http\Response
     */
    public function report()
    {
        $student = Student::with(['grades.course.teachers'])->where('user_id', Auth::id())->firstOrFail();
        
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
        
        $stats = [
            'gpa' => $gpa,
            'earned_credits' => $earnedCredits,
            'total_program_credits' => 120, // Example total program credits
            'passed_courses' => $passedCourses,
            'failed_courses' => $failedCourses,
            'current_courses' => $currentCourses->count(),
            'current_semester_gpa' => $currentSemesterGPA,
            'overall_score' => $student->grades->avg('score') ?? 0,
            'success_rate' => $student->grades->count() > 0 
                ? ($passedCourses / $student->grades->count()) * 100 
                : 0,
        ];
        
        return view('student.grades.report', compact(
            'student',
            'currentSemester',
            'currentCourses',
            'academicHistory',
            'semesterGPA',
            'semesterCredits',
            'gradeDistribution', 
            'stats'
        ));
    }
} 