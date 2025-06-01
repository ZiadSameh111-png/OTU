<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Course;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    /**
     * Display a listing of grades.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            return $this->adminIndex();
        } elseif ($user->hasRole('Teacher')) {
            return $this->teacherIndex();
        } elseif ($user->hasRole('Student')) {
            return $this->studentIndex();
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized access'
        ], 403);
    }

    /**
     * Display grades for admin.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function adminIndex()
    {
        $courses = Course::with(['teachers', 'groups.students'])->get();
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

        return response()->json([
            'status' => 'success',
            'data' => [
                'courses' => $courses,
                'groups' => $groups,
                'courseStats' => $courseStats
            ]
        ]);
    }

    /**
     * Display grades for teacher.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function teacherIndex()
    {
        $teacher = Auth::user();
        $courses = $teacher->teacherCourses;
        
        $gradesData = [];
        foreach ($courses as $course) {
            $courseData = [
                'course' => $course,
                'groups' => [],
            ];
            
            foreach ($course->groups as $group) {
                $students = $group->students;
                $grades = Grade::where('course_id', $course->id)
                    ->whereIn('student_id', $students->pluck('id'))
                    ->get();
                    
                $courseData['groups'][] = [
                    'group' => $group,
                    'students' => $students,
                    'grades' => $grades,
                ];
            }
            
            $gradesData[] = $courseData;
        }

        return response()->json([
            'status' => 'success',
            'data' => $gradesData
        ]);
    }

    /**
     * Display grades for student.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function studentIndex()
    {
        $student = Auth::user();
        $grades = Grade::where('student_id', $student->id)
            ->with('course')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $grades
        ]);
    }

    /**
     * Store a newly created grade in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('Teacher')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'assignment_grade' => 'required|numeric|min:0|max:100',
            'midterm_grade' => 'required|numeric|min:0|max:100',
            'final_grade' => 'required|numeric|min:0|max:100',
            'practical_grade' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if teacher is assigned to this course
        $course = Course::find($request->course_id);
        if (!$course->teachers->contains(Auth::id())) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to grade this course'
            ], 403);
        }

        // Check if student is in a group assigned to this course
        $student = User::find($request->student_id);
        if (!$student->group || !$course->groups->contains($student->group_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student is not in a group assigned to this course'
            ], 422);
        }

        $grade = Grade::create([
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
            'assignment_grade' => $request->assignment_grade,
            'midterm_grade' => $request->midterm_grade,
            'final_grade' => $request->final_grade,
            'practical_grade' => $request->practical_grade,
            'notes' => $request->notes,
            'submitted' => true,
            'submission_date' => Carbon::now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Grade created successfully',
            'data' => $grade
        ], 201);
    }

    /**
     * Display the specified grade.
     *
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Grade $grade)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $grade->course->teachers->contains($user->id)) &&
            !($user->hasRole('Student') && $grade->student_id === $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $grade->load(['course', 'student']);

        return response()->json([
            'status' => 'success',
            'data' => $grade
        ]);
    }

    /**
     * Update the specified grade in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Grade $grade)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Teacher') || !$grade->course->teachers->contains($user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'assignment_grade' => 'required|numeric|min:0|max:100',
            'midterm_grade' => 'required|numeric|min:0|max:100',
            'final_grade' => 'required|numeric|min:0|max:100',
            'practical_grade' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $grade->update([
            'assignment_grade' => $request->assignment_grade,
            'midterm_grade' => $request->midterm_grade,
            'final_grade' => $request->final_grade,
            'practical_grade' => $request->practical_grade,
            'notes' => $request->notes,
            'submitted' => true,
            'submission_date' => Carbon::now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Grade updated successfully',
            'data' => $grade
        ]);
    }

    /**
     * Get course report with statistics.
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
            ->where('submitted', true)
            ->with(['student'])
            ->get();

        // Calculate component averages
        $componentAverages = [
            'coursework_avg' => $grades->avg('assignment_grade') ?? 0,
            'midterm_avg' => $grades->avg('midterm_grade') ?? 0,
            'final_avg' => $grades->avg('final_grade') ?? 0,
            'practical_avg' => $grades->avg('practical_grade') ?? 0
        ];

        // Get top performing students (top 5)
        $topStudents = $grades->sortByDesc('total')
            ->take(5)
            ->map(function ($grade) {
                return [
                    'name' => $grade->student->name,
                    'total_percentage' => $grade->total,
                    'grade' => $grade->getLetterGradeAttribute()
                ];
            });

        // Get students at risk (below 60%)
        $studentsAtRisk = $grades->filter(function ($grade) {
            return $grade->total < 60;
        })
        ->map(function ($grade) {
            return [
                'name' => $grade->student->name,
                'total_percentage' => $grade->total,
                'grade' => $grade->getLetterGradeAttribute()
            ];
        });

        // Group performance statistics
        $groupPerformance = [];
        foreach ($course->groups as $group) {
            $groupGrades = $grades->whereIn('student_id', $group->students->pluck('id'));
            if ($groupGrades->count() > 0) {
                $groupPerformance[] = [
                    'name' => $group->name,
                    'student_count' => $groupGrades->count(),
                    'average_score' => $groupGrades->avg('total') ?? 0
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'course' => $course,
                'total_students' => $grades->count(),
                'component_averages' => $componentAverages,
                'top_students' => $topStudents->values(),
                'students_at_risk' => $studentsAtRisk->values(),
                'group_performance' => $groupPerformance
            ]
        ]);
    }

    /**
     * Get group report with statistics.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function groupReport(Group $group)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $group->load(['students', 'courses.teacher']);
        
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
                    
                    if ($grade->total >= 60) {
                        $courseGrades['passed']++;
                    } else {
                        $courseGrades['failed']++;
                    }
                    
                    $totalScore += $grade->total;
                    $scoreCount++;
                }
            }
            
            $courseGrades['average'] = $scoreCount > 0 ? round($totalScore / $scoreCount, 2) : 0;
            $courseGrades['submission_rate'] = $totalStudents > 0 ? 
                round(($courseGrades['submitted'] / $totalStudents) * 100) : 0;
            
            $courseStats[$course->id] = $courseGrades;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'group' => $group,
                'course_statistics' => $courseStats
            ]
        ]);
    }
} 