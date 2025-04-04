<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'fee_id',
        'amount',
        'payment_method',
        'status',
        'transaction_id',
        'description',
        'paid_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'paid_at' => 'datetime',
    ];

    /**
     * العلاقة مع المستخدم الذي قام بالدفع
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع الرسوم المرتبطة بالمعاملة
     */
    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    /**
     * الحصول على اسم حالة المعاملة بالعربية
     */
    public function getStatusNameAttribute()
    {
        switch ($this->status) {
            case 'completed':
                return 'مكتملة';
            case 'pending':
                return 'قيد المعالجة';
            case 'failed':
                return 'فاشلة';
            case 'cancelled':
                return 'ملغية';
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
            case 'credit_card':
                return 'بطاقة ائتمان';
            case 'bank_transfer':
                return 'تحويل بنكي';
            case 'cash':
                return 'نقدي';
            default:
                return $this->payment_method;
        }
    }
}
