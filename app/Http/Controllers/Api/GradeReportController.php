<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class GradeReportController extends Controller
{
    /**
     * Generate student grade report.
     *
     * @param  \App\Models\User  $student
     * @return \Illuminate\Http\JsonResponse
     */
    public function studentReport(User $student)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $user->teacherCourses()->whereHas('groups', function($q) use ($student) {
                $q->where('id', $student->group_id);
            })->exists()) &&
            !($user->id === $student->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Get all grades for the student
        $grades = Grade::where('student_id', $student->id)
            ->with('course')
            ->get();

        // Calculate overall statistics
        $totalCourses = $grades->count();
        $completedCourses = $grades->where('submitted', true)->count();
        $totalGradePoints = 0;
        $passedCourses = 0;

        $courseGrades = [];
        foreach ($grades as $grade) {
            $courseGrade = [
                'course' => $grade->course,
                'assignment_grade' => $grade->assignment_grade,
                'midterm_grade' => $grade->midterm_grade,
                'final_grade' => $grade->final_grade,
                'practical_grade' => $grade->practical_grade,
                'total' => $grade->total,
                'letter_grade' => $grade->getLetterGradeAttribute(),
                'submitted' => $grade->submitted,
                'submission_date' => $grade->submission_date
            ];

            if ($grade->submitted) {
                $totalGradePoints += $grade->total;
                if ($grade->total >= 60) {
                    $passedCourses++;
                }
            }

            $courseGrades[] = $courseGrade;
        }

        $gpa = $completedCourses > 0 ? round($totalGradePoints / $completedCourses, 2) : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'group' => $student->group
                ],
                'statistics' => [
                    'total_courses' => $totalCourses,
                    'completed_courses' => $completedCourses,
                    'passed_courses' => $passedCourses,
                    'failed_courses' => $completedCourses - $passedCourses,
                    'gpa' => $gpa,
                    'success_rate' => $completedCourses > 0 ? round(($passedCourses / $completedCourses) * 100, 2) : 0
                ],
                'course_grades' => $courseGrades
            ]
        ]);
    }

    /**
     * Generate course grade report.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseReport(Course $course)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $course->teachers->contains($user->id))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $grades = Grade::where('course_id', $course->id)
            ->with('student')
            ->get();

        // Calculate course statistics
        $totalStudents = $grades->count();
        $submittedGrades = $grades->where('submitted', true);
        $completedCount = $submittedGrades->count();
        $passedCount = $submittedGrades->where('total', '>=', 60)->count();
        $failedCount = $completedCount - $passedCount;

        // Calculate grade distribution
        $gradeDistribution = [
            'A' => $submittedGrades->filter(function($grade) { return $grade->total >= 90; })->count(),
            'B' => $submittedGrades->filter(function($grade) { return $grade->total >= 80 && $grade->total < 90; })->count(),
            'C' => $submittedGrades->filter(function($grade) { return $grade->total >= 70 && $grade->total < 80; })->count(),
            'D' => $submittedGrades->filter(function($grade) { return $grade->total >= 60 && $grade->total < 70; })->count(),
            'F' => $submittedGrades->filter(function($grade) { return $grade->total < 60; })->count(),
        ];

        // Calculate component averages
        $componentAverages = [
            'assignment' => $submittedGrades->avg('assignment_grade'),
            'midterm' => $submittedGrades->avg('midterm_grade'),
            'final' => $submittedGrades->avg('final_grade'),
            'practical' => $submittedGrades->avg('practical_grade'),
        ];

        // Get top performers
        $topPerformers = $submittedGrades
            ->sortByDesc('total')
            ->take(5)
            ->map(function($grade) {
                return [
                    'student' => $grade->student,
                    'total' => $grade->total,
                    'letter_grade' => $grade->getLetterGradeAttribute()
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'course' => $course,
                'statistics' => [
                    'total_students' => $totalStudents,
                    'completed_count' => $completedCount,
                    'passed_count' => $passedCount,
                    'failed_count' => $failedCount,
                    'completion_rate' => $totalStudents > 0 ? round(($completedCount / $totalStudents) * 100, 2) : 0,
                    'success_rate' => $completedCount > 0 ? round(($passedCount / $completedCount) * 100, 2) : 0,
                    'average_grade' => round($submittedGrades->avg('total'), 2),
                    'highest_grade' => $submittedGrades->max('total'),
                    'lowest_grade' => $submittedGrades->min('total')
                ],
                'grade_distribution' => $gradeDistribution,
                'component_averages' => $componentAverages,
                'top_performers' => $topPerformers
            ]
        ]);
    }

    /**
     * Generate group grade report.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function groupReport(Group $group)
    {
        $user = Auth::user();

        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $user->teacherCourses()->whereHas('groups', function($q) use ($group) {
                $q->where('id', $group->id);
            })->exists())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $students = $group->students;
        $courses = $group->courses;

        $courseStats = [];
        foreach ($courses as $course) {
            $grades = Grade::where('course_id', $course->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get();

            $submittedGrades = $grades->where('submitted', true);
            $completedCount = $submittedGrades->count();
            $passedCount = $submittedGrades->where('total', '>=', 60)->count();

            $courseStats[] = [
                'course' => $course,
                'total_students' => $grades->count(),
                'completed_count' => $completedCount,
                'passed_count' => $passedCount,
                'failed_count' => $completedCount - $passedCount,
                'average_grade' => round($submittedGrades->avg('total'), 2),
                'highest_grade' => $submittedGrades->max('total'),
                'lowest_grade' => $submittedGrades->min('total'),
                'completion_rate' => $grades->count() > 0 ? round(($completedCount / $grades->count()) * 100, 2) : 0,
                'success_rate' => $completedCount > 0 ? round(($passedCount / $completedCount) * 100, 2) : 0
            ];
        }

        // Calculate student performance
        $studentStats = [];
        foreach ($students as $student) {
            $grades = Grade::whereIn('course_id', $courses->pluck('id'))
                ->where('student_id', $student->id)
                ->where('submitted', true)
                ->get();

            $totalGrades = 0;
            $passedCourses = 0;
            foreach ($grades as $grade) {
                $totalGrades += $grade->total;
                if ($grade->total >= 60) {
                    $passedCourses++;
                }
            }

            $studentStats[] = [
                'student' => $student,
                'completed_courses' => $grades->count(),
                'passed_courses' => $passedCourses,
                'failed_courses' => $grades->count() - $passedCourses,
                'average_grade' => $grades->count() > 0 ? round($totalGrades / $grades->count(), 2) : 0,
                'success_rate' => $grades->count() > 0 ? round(($passedCourses / $grades->count()) * 100, 2) : 0
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'group' => $group,
                'course_statistics' => $courseStats,
                'student_statistics' => $studentStats
            ]
        ]);
    }

    /**
     * Generate semester grade report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function semesterReport(Request $request)
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'semester' => 'required|string',
            'year' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get all grades for the semester
        $grades = Grade::whereHas('course', function($q) use ($request) {
            $q->where('semester', $request->semester)
                ->where('year', $request->year);
        })->with(['student', 'course'])->get();

        // Calculate overall statistics
        $totalStudents = User::whereHas('roles', function($q) {
            $q->where('name', 'Student');
        })->count();

        $submittedGrades = $grades->where('submitted', true);
        $totalGrades = $submittedGrades->count();
        $passedGrades = $submittedGrades->where('total', '>=', 60)->count();

        // Calculate grade distribution
        $gradeDistribution = [
            'A' => $submittedGrades->filter(function($grade) { return $grade->total >= 90; })->count(),
            'B' => $submittedGrades->filter(function($grade) { return $grade->total >= 80 && $grade->total < 90; })->count(),
            'C' => $submittedGrades->filter(function($grade) { return $grade->total >= 70 && $grade->total < 80; })->count(),
            'D' => $submittedGrades->filter(function($grade) { return $grade->total >= 60 && $grade->total < 70; })->count(),
            'F' => $submittedGrades->filter(function($grade) { return $grade->total < 60; })->count(),
        ];

        // Get top performing students
        $studentAverages = [];
        $students = User::whereHas('roles', function($q) {
            $q->where('name', 'Student');
        })->get();

        foreach ($students as $student) {
            $studentGrades = $submittedGrades->where('student_id', $student->id);
            if ($studentGrades->count() > 0) {
                $studentAverages[] = [
                    'student' => $student,
                    'average' => round($studentGrades->avg('total'), 2),
                    'courses_completed' => $studentGrades->count()
                ];
            }
        }

        // Sort by average grade and get top 10
        $topStudents = collect($studentAverages)
            ->sortByDesc('average')
            ->take(10)
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'semester_info' => [
                    'semester' => $request->semester,
                    'year' => $request->year
                ],
                'statistics' => [
                    'total_students' => $totalStudents,
                    'total_grades' => $totalGrades,
                    'passed_grades' => $passedGrades,
                    'failed_grades' => $totalGrades - $passedGrades,
                    'average_grade' => round($submittedGrades->avg('total'), 2),
                    'success_rate' => $totalGrades > 0 ? round(($passedGrades / $totalGrades) * 100, 2) : 0
                ],
                'grade_distribution' => $gradeDistribution,
                'top_students' => $topStudents
            ]
        ]);
    }
} 