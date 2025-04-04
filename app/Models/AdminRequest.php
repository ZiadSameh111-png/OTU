<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'details',
        'priority',
        'request_date',
        'status',
        'admin_comment',
        'admin_id',
        'attachment',
    ];

    protected $casts = [
        'request_date' => 'date',
    ];

    /**
     * الحصول على الطالب صاحب الطلب
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * الحصول على المسؤول الذي عالج الطلب
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * الحصول على اسم نوع الطلب بشكل مناسب للعرض
     */
    public function getTypeNameAttribute()
    {
        $types = [
            'leave' => 'طلب إجازة',
            'certificate_request' => 'طلب شهادة دراسية',
            'group_transfer' => 'طلب نقل مجموعة',
            'course_withdrawal' => 'طلب انسحاب من مقرر',
            'absence_excuse' => 'طلب عذر غياب',
            'transcript' => 'طلب كشف درجات',
            'other' => 'طلب آخر',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * الحصول على اسم حالة الطلب بشكل مناسب للعرض
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            'pending' => 'قيد المعالجة',
            'approved' => 'مقبول',
            'rejected' => 'مرفوض',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * الحصول على اسم أولوية الطلب بشكل مناسب للعرض
     */
    public function getPriorityNameAttribute()
    {
        $priorities = [
            'low' => 'منخفضة',
            'normal' => 'عادية',
            'high' => 'عالية',
            'urgent' => 'عاجلة',
        ];

        return $priorities[$this->priority] ?? $this->priority;
    }
}
