<?php

namespace App\Models;

class Student extends User
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'group_id',
    ];

    /**
     * Get all grades for the student.
     */
    public function grades()
    {
        return $this->hasMany(Grade::class, 'student_id', 'id');
    }

    /**
     * Get the group that owns the student.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get all exam attempts by the student.
     */
    public function examAttempts()
    {
        return $this->hasMany(StudentExamAttempt::class, 'student_id', 'id');
    }

    /**
     * The roles that belong to the student.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    /**
     * Get the user associated with the student.
     * Since Student extends User, this relationship returns the same model instance.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::addGlobalScope('student', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', 'Student');
            });
        });
    }
} 