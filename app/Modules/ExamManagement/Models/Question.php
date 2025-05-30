<?php

namespace App\Modules\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = [
        'exam_id',
        'question_text',
        'question_type', // multiple_choice, true_false, essay
        'points',
        'order',
        'correct_answer',
        'options' // JSON field for multiple choice options
    ];

    protected $casts = [
        'points' => 'float',
        'order' => 'integer',
        'options' => 'array'
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
} 