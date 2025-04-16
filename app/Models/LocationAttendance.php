<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_setting_id',
        'attendance_date',
        'attendance_time',
        'latitude',
        'longitude',
        'distance_meters',
        'is_within_range',
        'status',
        'notes',
        'device_info',
        'ip_address',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'attendance_time' => 'datetime',
        'is_within_range' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع إعدادات الموقع
     */
    public function locationSetting()
    {
        return $this->belongsTo(LocationSetting::class);
    }

    /**
     * تحقق مما إذا كان المستخدم سجل حضوره اليوم لموقع معين
     */
    public static function hasAttendedToday($userId, $locationId)
    {
        return self::where('user_id', $userId)
            ->where('location_setting_id', $locationId)
            ->whereDate('attendance_date', now()->toDateString())
            ->exists();
    }

    /**
     * الحصول على حضور اليوم للمستخدم
     */
    public static function getTodayAttendance($userId)
    {
        return self::with('locationSetting')
            ->where('user_id', $userId)
            ->whereDate('attendance_date', now()->toDateString())
            ->get();
    }
}
