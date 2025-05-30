<?php

namespace App\Modules\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exam extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'duration_minutes',
        'passing_score',
        'course_id',
        'created_by',
        'status'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'passing_score' => 'float',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ExamSubmission::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
} 