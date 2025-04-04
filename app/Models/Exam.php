<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Exam extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'course_id',
        'group_id',
        'teacher_id',
        'start_time',
        'end_time',
        'duration',
        'status',
        'question_type',
        'total_marks',
        'is_published',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_published' => 'boolean',
    ];

    /**
     * Get the course that owns the exam.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the group that owns the exam.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the teacher that created the exam.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the questions for the exam.
     */
    public function questions()
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    /**
     * Get the student attempts for the exam.
     */
    public function attempts()
    {
        return $this->hasMany(StudentExamAttempt::class);
    }

    /**
     * Get the student answers for the exam.
     */
    public function answers()
    {
        return $this->hasMany(StudentExamAnswer::class);
    }

    /**
     * Check if exam is active based on current time.
     *
     * @return bool
     */
    public function isActive()
    {
        $now = Carbon::now();
        return $this->is_published && 
               $now->greaterThanOrEqualTo($this->start_time) && 
               $now->lessThanOrEqualTo($this->end_time);
    }

    /**
     * Check if exam has ended.
     *
     * @return bool
     */
    public function hasEnded()
    {
        return Carbon::now()->greaterThan($this->end_time);
    }

    /**
     * Check if exam has not started yet.
     *
     * @return bool
     */
    public function notStartedYet()
    {
        return Carbon::now()->lessThan($this->start_time);
    }

    /**
     * Get total number of students who have attempted the exam.
     *
     * @return int
     */
    public function totalAttempts()
    {
        return $this->attempts()->count();
    }

    /**
     * Get total number of students who have completed the exam.
     *
     * @return int
     */
    public function totalCompleted()
    {
        return $this->attempts()->whereIn('status', ['submitted', 'graded'])->count();
    }

    /**
     * Get total number of students who have been graded.
     *
     * @return int
     */
    public function totalGraded()
    {
        return $this->attempts()->where('is_graded', true)->count();
    }

    /**
     * Calculate time remaining for the exam (in minutes).
     *
     * @return int
     */
    public function timeRemaining()
    {
        if ($this->hasEnded()) {
            return 0;
        }
        
        $now = Carbon::now();
        return $now->diffInMinutes($this->end_time, false);
    }

    /**
     * Update the status of the exam based on current time.
     *
     * @return void
     */
    public function updateStatus()
    {
        if ($this->notStartedYet()) {
            $this->status = 'pending';
        } elseif ($this->isActive()) {
            $this->status = 'active';
        } elseif ($this->hasEnded()) {
            $this->status = 'completed';
        }
        
        $this->save();
    }
}
