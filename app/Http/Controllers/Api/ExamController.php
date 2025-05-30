<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Course;
use App\Models\Group;
use App\Models\StudentExamAttempt;
use App\Models\StudentExamAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ExamController extends Controller
{
    /**
     * Display a listing of exams.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            $exams = Exam::with(['course', 'group'])->get();
        } elseif ($user->hasRole('Teacher')) {
            $exams = Exam::whereHas('course', function($query) use ($user) {
                $query->whereHas('teachers', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })->with(['course', 'group'])->get();
        } elseif ($user->hasRole('Student')) {
            $exams = Exam::where('group_id', $user->group_id)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->with(['course'])
                ->get();
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $exams
        ]);
    }

    /**
     * Store a newly created exam in storage.
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'group_id' => 'required|exists:groups,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|numeric|min:0',
            'passing_marks' => 'required|numeric|min:0|lte:total_marks',
            'questions' => 'required|array|min:1',
            'questions.*.content' => 'required|string',
            'questions.*.marks' => 'required|numeric|min:0',
            'questions.*.type' => 'required|in:multiple_choice,true_false,short_answer',
            'questions.*.options' => 'required_if:questions.*.type,multiple_choice|array',
            'questions.*.correct_answer' => 'required|string',
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
                'message' => 'You are not authorized to create exams for this course'
            ], 403);
        }

        // Check if group is assigned to this course
        if (!$course->groups->contains($request->group_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This group is not assigned to the course'
            ], 422);
        }

        $exam = Exam::create([
            'title' => $request->title,
            'description' => $request->description,
            'course_id' => $request->course_id,
            'group_id' => $request->group_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration_minutes' => $request->duration_minutes,
            'total_marks' => $request->total_marks,
            'passing_marks' => $request->passing_marks,
            'questions' => $request->questions,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Exam created successfully',
            'data' => $exam
        ], 201);
    }

    /**
     * Display the specified exam.
     *
     * @param  \App\Models\Exam  $exam
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Exam $exam)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $exam->course->teachers->contains($user->id)) &&
            !($user->hasRole('Student') && $exam->group_id === $user->group_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $exam->load(['course', 'group']);

        // If student, check if exam is currently available
        if ($user->hasRole('Student')) {
            if ($exam->start_time > now() || $exam->end_time < now()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Exam is not currently available'
                ], 403);
            }

            // Remove correct answers from questions for students
            $questions = collect($exam->questions)->map(function ($question) {
                unset($question['correct_answer']);
                return $question;
            });
            $exam->questions = $questions;
        }

        return response()->json([
            'status' => 'success',
            'data' => $exam
        ]);
    }

    /**
     * Update the specified exam in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Exam  $exam
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Exam $exam)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Teacher') || !$exam->course->teachers->contains($user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|numeric|min:0',
            'passing_marks' => 'required|numeric|min:0|lte:total_marks',
            'questions' => 'required|array|min:1',
            'questions.*.content' => 'required|string',
            'questions.*.marks' => 'required|numeric|min:0',
            'questions.*.type' => 'required|in:multiple_choice,true_false,short_answer',
            'questions.*.options' => 'required_if:questions.*.type,multiple_choice|array',
            'questions.*.correct_answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $exam->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration_minutes' => $request->duration_minutes,
            'total_marks' => $request->total_marks,
            'passing_marks' => $request->passing_marks,
            'questions' => $request->questions,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Exam updated successfully',
            'data' => $exam
        ]);
    }

    /**
     * Remove the specified exam from storage.
     *
     * @param  \App\Models\Exam  $exam
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Exam $exam)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Teacher') || !$exam->course->teachers->contains($user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Check if any student has already attempted the exam
        if (StudentExamAttempt::where('exam_id', $exam->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete exam because it has been attempted by students'
            ], 422);
        }

        $exam->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Exam deleted successfully'
        ]);
    }

    /**
     * Submit an exam attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Exam  $exam
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitAttempt(Request $request, Exam $exam)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Student') || $user->group_id !== $exam->group_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Check if exam is currently available
        if ($exam->start_time > now() || $exam->end_time < now()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exam is not currently available'
            ], 403);
        }

        // Check if student has already attempted this exam
        if (StudentExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $user->id)
            ->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already attempted this exam'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'answers' => 'required|array',
            'answers.*.question_index' => 'required|integer|min:0',
            'answers.*.answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Calculate score
        $totalScore = 0;
        $answers = collect($request->answers);
        $questions = collect($exam->questions);

        foreach ($answers as $answer) {
            $question = $questions[$answer['question_index']] ?? null;
            if ($question) {
                $isCorrect = strtolower($answer['answer']) === strtolower($question['correct_answer']);
                $score = $isCorrect ? $question['marks'] : 0;
                $totalScore += $score;

                StudentExamAnswer::create([
                    'exam_id' => $exam->id,
                    'student_id' => $user->id,
                    'question_index' => $answer['question_index'],
                    'student_answer' => $answer['answer'],
                    'is_correct' => $isCorrect,
                    'score' => $score,
                ]);
            }
        }

        // Create attempt record
        $attempt = StudentExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $user->id,
            'submission_time' => now(),
            'total_score' => $totalScore,
            'passed' => $totalScore >= $exam->passing_marks,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Exam submitted successfully',
            'data' => [
                'attempt' => $attempt,
                'total_score' => $totalScore,
                'passed' => $totalScore >= $exam->passing_marks,
            ]
        ]);
    }

    /**
     * Get exam results for a specific exam.
     *
     * @param  \App\Models\Exam  $exam
     * @return \Illuminate\Http\JsonResponse
     */
    public function examResults(Exam $exam)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Admin') && 
            !($user->hasRole('Teacher') && $exam->course->teachers->contains($user->id))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $attempts = StudentExamAttempt::where('exam_id', $exam->id)
            ->with(['student', 'answers'])
            ->get();

        $statistics = [
            'total_attempts' => $attempts->count(),
            'passed_count' => $attempts->where('passed', true)->count(),
            'failed_count' => $attempts->where('passed', false)->count(),
            'average_score' => $attempts->avg('total_score'),
            'highest_score' => $attempts->max('total_score'),
            'lowest_score' => $attempts->min('total_score'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'exam' => $exam,
                'attempts' => $attempts,
                'statistics' => $statistics
            ]
        ]);
    }

    /**
     * Get student's exam results.
     *
     * @param  \App\Models\Exam  $exam
     * @return \Illuminate\Http\JsonResponse
     */
    public function studentResults(Exam $exam)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Student') || $user->group_id !== $exam->group_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $attempt = StudentExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $user->id)
            ->with('answers')
            ->first();

        if (!$attempt) {
            return response()->json([
                'status' => 'error',
                'message' => 'No attempt found for this exam'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'exam' => $exam,
                'attempt' => $attempt,
            ]
        ]);
    }
} 