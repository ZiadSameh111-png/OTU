<?php

namespace App\Modules\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    protected $fillable = [
        'exam_submission_id',
        'question_id',
        'answer_text',
        'is_correct',
        'score',
        'grader_feedback'
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'score' => 'float'
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ExamSubmission::class, 'exam_submission_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
} 