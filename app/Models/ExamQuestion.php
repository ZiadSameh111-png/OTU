<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'exam_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'marks',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
        'marks' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the exam that owns the question.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the student answers for this question.
     */
    public function answers()
    {
        return $this->hasMany(StudentExamAnswer::class, 'question_id');
    }

    /**
     * Check if the provided answer is correct.
     *
     * @param string $answer
     * @return bool
     */
    public function isCorrect($answer)
    {
        if ($this->question_type === 'open_ended') {
            // Open-ended questions need manual grading
            return null;
        }
        
        if ($this->question_type === 'true_false') {
            return strtolower($answer) === strtolower($this->correct_answer);
        }
        
        if ($this->question_type === 'multiple_choice') {
            return $answer === $this->correct_answer;
        }
        
        return false;
    }

    /**
     * Get the question options as an array.
     *
     * @return array
     */
    public function getOptionsArray()
    {
        if ($this->question_type === 'multiple_choice') {
            return $this->options ?? [];
        }
        
        if ($this->question_type === 'true_false') {
            return ['true' => 'صح', 'false' => 'خطأ'];
        }
        
        return [];
    }
    
    /**
     * Get the formatted question type in Arabic.
     *
     * @return string
     */
    public function getQuestionTypeArabic()
    {
        $types = [
            'multiple_choice' => 'اختيار متعدد',
            'true_false' => 'صح وخطأ',
            'open_ended' => 'سؤال مفتوح',
        ];
        
        return $types[$this->question_type] ?? $this->question_type;
    }
}
