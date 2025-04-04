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
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * العلاقة مع الطلاب المنتمين للمجموعة
     */
    public function students()
    {
        return $this->hasMany(User::class, 'group_id')
            ->whereHas('roles', function($query) {
                $query->where('name', 'Student');
            });
    }

    /**
     * العلاقة مع المقررات التي تدرسها المجموعة
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    /**
     * العلاقة مع الجداول الدراسية
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * العلاقة مع الاختبارات المخصصة للمجموعة
     */
    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
