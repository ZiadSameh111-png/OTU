<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'group_id',
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
     * Get the group that the schedule belongs to.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
