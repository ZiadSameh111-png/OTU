<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        return !! $role->intersect($this->roles)->count();
    }

    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::whereName($role)->firstOrCreate(['name' => $role]);
        }
        $this->roles()->syncWithoutDetaching($role);
    }

    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Role::whereName($role)->first();
        }
        $this->roles()->detach($role);
    }

    /**
     * العلاقة مع المجموعة التي ينتمي إليها المستخدم
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the schedules for the student based on their group.
     */
    public function schedules()
    {
        return $this->group ? $this->group->schedules() : collect();
    }

    /**
     * Get the courses that the user teaches (for teachers only).
     */
    public function teacherCourses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    /**
     * Get the courses for the student based on their group.
     */
    public function studentCourses()
    {
        return $this->group ? $this->group->courses() : collect();
    }

    /**
     * Get the admin requests submitted by this user.
     */
    public function adminRequests()
    {
        return $this->hasMany(AdminRequest::class, 'user_id');
    }
}
