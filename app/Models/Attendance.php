<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the teacher that the attendance is for.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if the status is present.
     */
    public function isPresent()
    {
        return $this->status === 'present';
    }

    /**
     * Check if the status is absent.
     */
    public function isAbsent()
    {
        return $this->status === 'absent';
    }
} 