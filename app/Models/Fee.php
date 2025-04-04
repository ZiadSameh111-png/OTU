<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'due_date',
        'description',
        'status',
        'academic_year',
        'fee_type'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * العلاقة مع المستخدم الذي أنشأ الرسم
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع المجموعات التي تنطبق عليها هذه الرسوم
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'fee_group');
    }

    /**
     * العلاقة مع المستخدم المرتبط بالرسوم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع مدفوعات هذه الرسوم
     */
    public function payments()
    {
        return $this->hasMany(FeePayment::class);
    }

    /**
     * تحديث رصيد المبلغ المدفوع والمتبقي
     */
    public function updateBalances()
    {
        $this->paid_amount = $this->payments()->where('status', 'completed')->sum('amount');
        $this->remaining_amount = max(0, $this->total_amount - $this->paid_amount);
        
        // تحديث حالة الرسوم بناءً على المبلغ المدفوع
        if ($this->remaining_amount <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'unpaid';
        }
        
        $this->save();
        
        return $this;
    }

    /**
     * الحصول على اسم حالة الرسوم بالعربية
     */
    public function getStatusNameAttribute()
    {
        switch ($this->status) {
            case 'paid':
                return 'مدفوع';
            case 'partial':
                return 'مدفوع جزئيًا';
            case 'unpaid':
                return 'غير مدفوع';
            case 'cancelled':
                return 'ملغي';
            case 'overdue':
                return 'متأخر';
            default:
                return 'غير محدد';
        }
    }

    /**
     * الحصول على اسم نوع الرسوم بالعربية
     */
    public function getFeeTypeNameAttribute()
    {
        switch ($this->fee_type) {
            case 'tuition':
                return 'رسوم دراسية';
            case 'registration':
                return 'رسوم تسجيل';
            case 'exam':
                return 'رسوم امتحانات';
            case 'other':
                return 'رسوم أخرى';
            default:
                return $this->fee_type;
        }
    }

    /**
     * الحصول على نسبة الدفع
     */
    public function getPaymentPercentageAttribute()
    {
        if ($this->total_amount > 0) {
            return round(($this->paid_amount / $this->total_amount) * 100, 2);
        }
        
        return 0;
    }

    /**
     * فحص ما إذا كان تاريخ استحقاق الرسوم قد انتهى
     */
    public function isOverdue()
    {
        return $this->due_date && $this->due_date->isPast() && $this->remaining_amount > 0;
    }

    /**
     * الحصول على حالة دفع الرسوم (مدفوع، غير مدفوع، مدفوع جزئيًا)
     */
    public function getPaymentStatusAttribute()
    {
        $remaining = $this->remaining_amount;
        
        if ($remaining <= 0) {
            return 'paid';
        } elseif ($remaining < $this->total_amount) {
            return 'partial';
        } else {
            return 'unpaid';
        }
    }

    /**
     * هل تم دفع كامل المبلغ؟
     */
    public function getIsPaidAttribute()
    {
        return $this->status === 'paid';
    }

    /**
     * الحصول على اسم حالة الدفع بالعربية
     */
    public function getPaymentStatusNameAttribute()
    {
        switch ($this->status) {
            case 'paid':
                return 'مدفوع';
            case 'partial':
                return 'مدفوع جزئيًا';
            case 'unpaid':
                return 'غير مدفوع';
            default:
                return 'غير محدد';
        }
    }

    /**
     * العلاقة مع الطالب المرتبط بالرسوم
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * الحصول على تاريخ آخر دفعة
     */
    public function getLastPaymentDateAttribute()
    {
        $lastPayment = $this->payments()->latest('payment_date')->first();
        
        return $lastPayment ? $lastPayment->payment_date : null;
    }
}
