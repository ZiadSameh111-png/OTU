<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'credit_hours',
        'semester',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    /**
     * Get the teachers for the course.
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'course_teacher', 'course_id', 'teacher_id')
                    ->whereHas('roles', function($query) {
                        $query->where('name', 'Teacher');
                    });
    }

    /**
     * Get the primary teacher for the course (first teacher for backward compatibility).
     */
    public function teacher()
    {
        return $this->belongsToMany(User::class, 'course_teacher', 'course_id', 'teacher_id')
                    ->whereHas('roles', function($query) {
                        $query->where('name', 'Teacher');
                    })
                    ->limit(1);
    }

    /**
     * Get the groups for the course.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'course_group');
    }

    /**
     * Get the grades for the course.
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Get the exams for the course.
     */
    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    /**
     * Get the schedules for the course.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get the primary teacher as an attribute (for backward compatibility).
     */
    public function getTeacherAttribute()
    {
        return $this->teachers()->first();
    }
}
