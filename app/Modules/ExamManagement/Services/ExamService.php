<?php

namespace App\Modules\ExamManagement\Services;

use App\Modules\ExamManagement\Models\{Exam, Question, ExamSubmission, Answer};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExamService
{
    public function createExam(array $data)
    {
        return DB::transaction(function () use ($data) {
            $exam = Exam::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'duration_minutes' => $data['duration_minutes'],
                'passing_score' => $data['passing_score'],
                'course_id' => $data['course_id'],
                'created_by' => auth()->id(),
                'status' => 'draft'
            ]);

            if (isset($data['questions'])) {
                foreach ($data['questions'] as $index => $questionData) {
                    $exam->questions()->create([
                        'question_text' => $questionData['text'],
                        'question_type' => $questionData['type'],
                        'points' => $questionData['points'],
                        'order' => $index + 1,
                        'correct_answer' => $questionData['correct_answer'] ?? null,
                        'options' => $questionData['options'] ?? null
                    ]);
                }
            }

            return $exam;
        });
    }

    public function startExam(Exam $exam, $userId)
    {
        if ($exam->status !== 'published') {
            throw new \Exception('Exam is not available');
        }

        if (Carbon::now()->lt($exam->start_time)) {
            throw new \Exception('Exam has not started yet');
        }

        if (Carbon::now()->gt($exam->end_time)) {
            throw new \Exception('Exam has ended');
        }

        $existingSubmission = $exam->submissions()
            ->where('user_id', $userId)
            ->where('status', '!=', 'submitted')
            ->first();

        if ($existingSubmission) {
            return $existingSubmission;
        }

        return $exam->submissions()->create([
            'user_id' => $userId,
            'start_time' => Carbon::now(),
            'status' => 'in_progress'
        ]);
    }

    public function submitAnswer(ExamSubmission $submission, Question $question, string $answer)
    {
        if ($submission->status !== 'in_progress') {
            throw new \Exception('Submission is not in progress');
        }

        $answer = $submission->answers()->updateOrCreate(
            ['question_id' => $question->id],
            ['answer_text' => $answer]
        );

        if ($question->question_type !== 'essay') {
            $isCorrect = strtolower($answer->answer_text) === strtolower($question->correct_answer);
            $score = $isCorrect ? $question->points : 0;
            
            $answer->update([
                'is_correct' => $isCorrect,
                'score' => $score
            ]);
        }

        return $answer;
    }

    public function submitExam(ExamSubmission $submission)
    {
        if ($submission->status !== 'in_progress') {
            throw new \Exception('Submission is not in progress');
        }

        $submission->update([
            'status' => 'submitted',
            'submit_time' => Carbon::now()
        ]);

        $this->autoGradeObjectiveQuestions($submission);

        return $submission;
    }

    protected function autoGradeObjectiveQuestions(ExamSubmission $submission)
    {
        $totalScore = 0;
        $answers = $submission->answers()
            ->whereHas('question', function ($query) {
                $query->whereIn('question_type', ['multiple_choice', 'true_false']);
            })
            ->get();

        foreach ($answers as $answer) {
            $totalScore += $answer->score ?? 0;
        }

        $submission->update(['total_score' => $totalScore]);
    }

    public function gradeEssayQuestion(Answer $answer, float $score, string $feedback)
    {
        if ($answer->question->question_type !== 'essay') {
            throw new \Exception('This is not an essay question');
        }

        $answer->update([
            'score' => $score,
            'grader_feedback' => $feedback
        ]);

        $submission = $answer->submission;
        $totalScore = $submission->answers()->sum('score');
        
        $submission->update([
            'total_score' => $totalScore,
            'graded_by' => auth()->id(),
            'graded_at' => Carbon::now(),
            'status' => 'graded'
        ]);

        return $answer;
    }
} 