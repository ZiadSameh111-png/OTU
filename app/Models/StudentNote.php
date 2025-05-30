<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentNote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id',
        'author_id',
        'title',
        'content',
    ];

    /**
     * Get the student that owns the note.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the author of the note.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
} 