<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'student_id',
        'day',
        'start_time',
        'end_time',
        'room',
    ];

    /**
     * Get the course that the schedule belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the student that the schedule belongs to.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
