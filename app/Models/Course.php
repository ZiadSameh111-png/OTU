<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description'
    ];

    /**
     * Get the schedules for the course.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
