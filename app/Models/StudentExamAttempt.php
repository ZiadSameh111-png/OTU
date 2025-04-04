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
     * @return int
     */
    public function timeRemaining()
    {
        if (!$this->start_time || $this->status === 'submitted' || $this->status === 'graded') {
            return 0;
        }
        
        $exam = $this->exam;
        if (!$exam) {
            return 0;
        }
        
        // Calculate the expected end time based on start time and exam duration
        $expectedEndTime = $this->start_time->copy()->addMinutes($exam->duration);
        
        // If exam end time is earlier than the expected end time based on duration,
        // use the exam end time as the cutoff
        $endTime = $expectedEndTime->min($exam->end_time);
        
        $now = Carbon::now();
        $remainingMinutes = $now->diffInMinutes($endTime, false);
        
        return max(0, $remainingMinutes);
    }

    /**
     * Check if the attempt is finished (either submitted or timed out).
     *
     * @return bool
     */
    public function isFinished()
    {
        return in_array($this->status, ['submitted', 'graded']) || $this->timeRemaining() <= 0;
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
        $this->status = 'submitted';
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
