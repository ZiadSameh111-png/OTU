<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    /**
     * Get the students that belong to the group.
     */
    public function students()
    {
        return $this->hasMany(User::class)->whereHas('roles', function ($query) {
            $query->where('name', 'Student');
        });
    }

    /**
     * Get the schedules that belong to the group.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * The courses that belong to the group.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_group');
    }
}
