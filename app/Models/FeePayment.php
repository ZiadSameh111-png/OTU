<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fee_id',
        'amount',
        'payment_method',
        'transaction_id',
        'payment_date',
        'status',
        'description',
        'notes'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    /**
     * العلاقة مع السجل الرئيسي للرسوم
     */
    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    /**
     * العلاقة مع المستخدم الذي قام بالدفع
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * تحديث حالة السجل الرئيسي للرسوم بعد عملية الدفع
     */
    public function updateFeeStatus()
    {
        if ($this->fee) {
            $this->fee->updateBalances();
        }
    }

    /**
     * الحصول على اسم حالة الدفع بالعربية
     */
    public function getStatusNameAttribute()
    {
        switch ($this->status) {
            case 'completed':
                return 'مكتمل';
            case 'pending':
                return 'قيد المعالجة';
            case 'failed':
                return 'فاشل';
            case 'cancelled':
                return 'ملغي';
            default:
                return $this->status;
        }
    }

    /**
     * الحصول على اسم طريقة الدفع بالعربية
     */
    public function getPaymentMethodNameAttribute()
    {
        switch ($this->payment_method) {
            case 'cash':
                return 'نقدي';
            case 'bank_transfer':
                return 'تحويل بنكي';
            case 'credit_card':
                return 'بطاقة ائتمان';
            case 'debit_card':
                return 'بطاقة خصم';
            case 'check':
                return 'شيك';
            default:
                return $this->payment_method;
        }
    }
} 