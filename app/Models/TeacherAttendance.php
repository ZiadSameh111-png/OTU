<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'teacher_id',
        'attendance_date',
        'check_in',
        'check_out',
        'status',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    /**
     * الحصول على المدرس المتعلق بسجل الحضور
     */
    public function teacher()
    {
        // Try teacher_id first, fall back to user_id if needed
        return $this->belongsTo(User::class, 'teacher_id')
            ->withDefault(function() {
                return $this->belongsTo(User::class, 'user_id')->first();
            });
    }

    /**
     * الحصول على المسؤول الذي سجل الحضور
     */
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * الحصول على اسم حالة الحضور بشكل مناسب للعرض
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            'present' => 'حاضر',
            'absent' => 'غائب',
            'late' => 'متأخر',
            'excused' => 'غياب بعذر',
            'on_leave' => 'في إجازة',
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}
