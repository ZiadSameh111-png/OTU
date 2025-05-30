<?php

namespace App\Modules\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSubmission extends Model
{
    protected $fillable = [
        'exam_id',
        'user_id',
        'start_time',
        'submit_time',
        'total_score',
        'status', // in_progress, submitted, graded
        'graded_by',
        'graded_at'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'submit_time' => 'datetime',
        'total_score' => 'float',
        'graded_at' => 'datetime'
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'graded_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
} 