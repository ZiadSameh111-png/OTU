<?php

namespace App\Modules\GradeReport\Services;

use App\Modules\ExamManagement\Models\{Exam, ExamSubmission};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GradeReportService
{
    public function generateStudentReport($userId, $courseId = null)
    {
        $query = ExamSubmission::where('user_id', $userId)
            ->where('status', 'graded')
            ->with(['exam', 'answers.question']);

        if ($courseId) {
            $query->whereHas('exam', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            });
        }

        $submissions = $query->get();

        return [
            'total_exams' => $submissions->count(),
            'average_score' => $submissions->avg('total_score'),
            'passed_exams' => $submissions->filter(function ($submission) {
                return $submission->total_score >= $submission->exam->passing_score;
            })->count(),
            'detailed_scores' => $submissions->map(function ($submission) {
                return [
                    'exam_title' => $submission->exam->title,
                    'score' => $submission->total_score,
                    'passing_score' => $submission->exam->passing_score,
                    'status' => $submission->total_score >= $submission->exam->passing_score ? 'Passed' : 'Failed',
                    'submission_date' => $submission->submit_time->format('Y-m-d H:i:s'),
                    'graded_date' => $submission->graded_at->format('Y-m-d H:i:s')
                ];
            })
        ];
    }

    public function generateCourseReport($courseId)
    {
        $exams = Exam::where('course_id', $courseId)->with('submissions')->get();

        return [
            'total_exams' => $exams->count(),
            'exam_statistics' => $exams->map(function ($exam) {
                $submissions = $exam->submissions->where('status', 'graded');
                $totalStudents = $submissions->count();
                $passedStudents = $submissions->filter(function ($submission) use ($exam) {
                    return $submission->total_score >= $exam->passing_score;
                })->count();

                return [
                    'exam_title' => $exam->title,
                    'total_students' => $totalStudents,
                    'passed_students' => $passedStudents,
                    'average_score' => $submissions->avg('total_score'),
                    'passing_rate' => $totalStudents > 0 ? ($passedStudents / $totalStudents) * 100 : 0,
                    'score_distribution' => $this->calculateScoreDistribution($submissions)
                ];
            })
        ];
    }

    public function generateExamReport(Exam $exam)
    {
        $submissions = $exam->submissions()->where('status', 'graded')->get();
        $totalStudents = $submissions->count();
        $passedStudents = $submissions->filter(function ($submission) use ($exam) {
            return $submission->total_score >= $exam->passing_score;
        })->count();

        return [
            'exam_details' => [
                'title' => $exam->title,
                'course' => $exam->course->name,
                'total_points' => $exam->questions->sum('points'),
                'passing_score' => $exam->passing_score
            ],
            'statistics' => [
                'total_submissions' => $totalStudents,
                'passed_students' => $passedStudents,
                'passing_rate' => $totalStudents > 0 ? ($passedStudents / $totalStudents) * 100 : 0,
                'average_score' => $submissions->avg('total_score'),
                'highest_score' => $submissions->max('total_score'),
                'lowest_score' => $submissions->min('total_score'),
                'score_distribution' => $this->calculateScoreDistribution($submissions)
            ],
            'question_analysis' => $this->analyzeQuestions($exam)
        ];
    }

    protected function calculateScoreDistribution(Collection $submissions)
    {
        $ranges = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0
        ];

        foreach ($submissions as $submission) {
            $score = $submission->total_score;
            switch (true) {
                case $score <= 20:
                    $ranges['0-20']++;
                    break;
                case $score <= 40:
                    $ranges['21-40']++;
                    break;
                case $score <= 60:
                    $ranges['41-60']++;
                    break;
                case $score <= 80:
                    $ranges['61-80']++;
                    break;
                default:
                    $ranges['81-100']++;
            }
        }

        return $ranges;
    }

    protected function analyzeQuestions(Exam $exam)
    {
        return $exam->questions->map(function ($question) {
            $answers = $question->answers()->whereHas('submission', function ($query) {
                $query->where('status', 'graded');
            })->get();

            $totalAnswers = $answers->count();
            $correctAnswers = $answers->where('is_correct', true)->count();

            return [
                'question_text' => $question->question_text,
                'type' => $question->question_type,
                'points' => $question->points,
                'total_answers' => $totalAnswers,
                'correct_answers' => $correctAnswers,
                'accuracy_rate' => $totalAnswers > 0 ? ($correctAnswers / $totalAnswers) * 100 : 0,
                'average_score' => $answers->avg('score')
            ];
        });
    }
} 