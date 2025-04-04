<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExamAnswer extends Model
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
        'question_id',
        'answer',
        'marks_obtained',
        'is_correct',
        'feedback',
        'submitted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_correct' => 'boolean',
        'marks_obtained' => 'integer',
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the student that owns the answer.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the exam that owns the answer.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the question that owns the answer.
     */
    public function question()
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }

    /**
     * Get the attempt that owns the answer.
     */
    public function attempt()
    {
        return $this->belongsTo(StudentExamAttempt::class, ['student_id', 'exam_id'], ['student_id', 'exam_id']);
    }

    /**
     * Automatically evaluate the answer if applicable.
     *
     * @return bool|null
     */
    public function evaluateAnswer()
    {
        // Get the associated question
        $question = $this->question;
        
        if (!$question) {
            return null;
        }

        // Don't evaluate open-ended questions
        if ($question->question_type === 'open_ended') {
            $this->is_correct = null;
            $this->marks_obtained = null;
            return null;
        }

        // Check if answer is correct
        $isCorrect = $question->isCorrect($this->answer);
        $this->is_correct = $isCorrect;
        
        // Assign marks if correct
        $this->marks_obtained = $isCorrect ? $question->marks : 0;
        
        // Save the evaluation
        $this->save();
        
        return $isCorrect;
    }
}
