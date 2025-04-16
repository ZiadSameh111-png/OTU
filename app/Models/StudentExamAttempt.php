<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StudentExamAttempt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id',
        'exam_id',
        'start_time',
        'submit_time',
        'total_marks_obtained',
        'total_possible_marks',
        'status',
        'is_graded',
        'graded_at',
        'graded_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_time' => 'datetime',
        'submit_time' => 'datetime',
        'graded_at' => 'datetime',
        'total_marks_obtained' => 'integer',
        'total_possible_marks' => 'integer',
        'is_graded' => 'boolean',
    ];

    /**
     * Get the student that owns the attempt.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the exam for this attempt.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the teacher who graded this attempt.
     */
    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get the answers for this attempt.
     */
    public function answers()
    {
        return $this->hasMany(StudentExamAnswer::class, 'student_id', 'student_id')
                    ->where('exam_id', $this->exam_id);
    }

    /**
     * Calculate duration of attempt in minutes.
     *
     * @return int
     */
    public function duration()
    {
        if (!$this->start_time) {
            return 0;
        }
        
        $endTime = $this->submit_time ?? Carbon::now();
        return $this->start_time->diffInMinutes($endTime);
    }

    /**
     * Calculate time remaining for the attempt (in minutes).
     *
     * @param  bool  $respectExamEndTime  Whether to consider the exam end time
     * @return int
     */
    public function timeRemaining($respectExamEndTime = true)
    {
        if (!$this->start_time || $this->status === 'submitted' || $this->status === 'graded') {
            return 0;
        }
        
        $exam = $this->exam;
        if (!$exam) {
            return 0;
        }
        
        $now = Carbon::now();
        
        // Calculate the expected end time based on start time and exam duration
        $expectedEndTime = $this->start_time->copy()->addMinutes($exam->duration);
        
        // If we need to respect the exam end time
        if ($respectExamEndTime) {
            // Return the time remaining until exam end time if it's earlier
            // than the expected attempt end time
            if ($now->lessThan($exam->end_time)) {
                // Return the minimum of time remaining for attempt and time remaining until exam end
                $attemptTimeRemaining = $now->diffInMinutes($expectedEndTime, false);
                $examTimeRemaining = $now->diffInMinutes($exam->end_time, false);
                
                return max(0, min($attemptTimeRemaining, $examTimeRemaining));
            } else {
                // Exam has ended
                return 0;
            }
        } else {
            // Don't consider exam end time, just calculate based on attempt duration
            $remainingMinutes = $now->diffInMinutes($expectedEndTime, false);
            return max(0, $remainingMinutes);
        }
    }
    
    /**
     * Calculate time remaining until exam end time (in minutes).
     *
     * @return int
     */
    public function timeRemainingUntilExamEnd()
    {
        $exam = $this->exam;
        if (!$exam) {
            return 0;
        }
        
        $now = Carbon::now();
        if ($now->greaterThan($exam->end_time)) {
            return 0;
        }
        
        return $now->diffInMinutes($exam->end_time, false);
    }

    /**
     * Check if the attempt is finished.
     *
     * @return bool
     */
    public function isFinished()
    {
        return in_array($this->status, ['pending_review', 'submitted', 'graded']) || $this->timeRemaining(false) <= 0;
    }

    /**
     * Get the score as a percentage.
     *
     * @return float
     */
    public function scorePercentage()
    {
        if (!$this->total_possible_marks || !$this->total_marks_obtained) {
            return 0;
        }
        
        return round(($this->total_marks_obtained / $this->total_possible_marks) * 100, 1);
    }

    /**
     * Mark the attempt as submitted.
     *
     * @return void
     */
    public function markAsSubmitted()
    {
        $this->status = 'pending_review';
        $this->submit_time = Carbon::now();
        
        // Calculate total marks for automatically graded questions
        $this->calculateTotalMarks();
        
        $this->save();
    }

    /**
     * Calculate the total marks from answers and update the attempt.
     *
     * @return void
     */
    public function calculateTotalMarks()
    {
        $totalMarks = 0;
        $totalPossibleMarks = 0;
        $hasOpenEndedQuestions = false;
        
        // Get all the answers for this attempt
        $answers = $this->answers()->with('question')->get();
        
        foreach ($answers as $answer) {
            $question = $answer->question;
            
            if (!$question) {
                continue;
            }
            
            $totalPossibleMarks += $question->marks;
            
            if ($question->question_type === 'open_ended') {
                $hasOpenEndedQuestions = true;
                // For open-ended questions, use the manually assigned marks if available
                if ($answer->marks_obtained !== null) {
                    $totalMarks += $answer->marks_obtained;
                }
            } else {
                // For automatically graded questions, add the marks
                $totalMarks += $answer->marks_obtained ?? 0;
            }
        }
        
        $this->total_marks_obtained = $totalMarks;
        $this->total_possible_marks = $totalPossibleMarks;
        
        // If there are no open-ended questions, mark as graded
        if (!$hasOpenEndedQuestions) {
            $this->is_graded = true;
            $this->graded_at = Carbon::now();
            $this->status = 'graded';
        }
        
        $this->save();
    }
}
