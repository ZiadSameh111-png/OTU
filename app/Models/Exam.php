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
        'duration',
        'status',
        'question_type',
        'total_marks',
        'is_published',
        'is_open',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_published' => 'boolean',
        'is_open' => 'boolean',
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
     * Get the teachers for the exam through the course.
     */
    public function teachers()
    {
        return $this->hasManyThrough(
            User::class,
            Course::class,
            'id', // Foreign key on courses table
            'id', // Foreign key on users table
            'course_id', // Local key on exams table
            'id' // Local key on courses table
        )->whereHas('roles', function($query) {
            $query->where('name', 'Teacher');
        });
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
     * Check if exam is active based on manual control.
     *
     * @return bool
     */
    public function isActive()
    {
        // الاختبار نشط إذا كان منشور ومفتوح
        return $this->is_published && $this->is_open;
    }

    /**
     * Check if exam has ended.
     *
     * @return bool
     */
    public function hasEnded()
    {
        // الاختبار منته إذا كان منشور وغير مفتوح
        return $this->is_published && !$this->is_open;
    }

    /**
     * Check if exam has not started yet.
     *
     * @return bool
     */
    public function notStartedYet()
    {
        // الاختبار لم يبدأ بعد إذا كان غير منشور
        return !$this->is_published;
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
     * Get the duration of the exam (in minutes).
     *
     * @return int
     */
    public function timeRemaining()
    {
        // في النظام الجديد نعتمد فقط على مدة الاختبار
        return $this->duration;
    }

    /**
     * Update the status of the exam based on is_open.
     *
     * @return void
     */
    public function updateStatus()
    {
        $oldStatus = $this->status;
        
        // تحديد الحالة بناءً على حالة النشر والفتح
        if (!$this->is_published) {
            $this->status = 'pending';
        } elseif ($this->is_published && $this->is_open) {
            $this->status = 'active';
        } elseif ($this->is_published && !$this->is_open) {
            $this->status = 'completed';
        }
        
        // حفظ الحالة الجديدة فقط إذا تغيرت
        if ($oldStatus !== $this->status) {
            \Log::debug('Status changed from ' . $oldStatus . ' to ' . $this->status);
            $this->save();
        }
    }

    /**
     * Open the exam manually.
     *
     * @return void
     */
    public function openExam()
    {
        $this->is_open = true;
        $this->status = 'active';
        $this->save();
    }

    /**
     * Close the exam manually.
     *
     * @return void
     */
    public function closeExam()
    {
        $this->is_open = false;
        $this->status = 'completed';
        $this->save();
    }
}
