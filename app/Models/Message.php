<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'body',
        'is_read',
        'is_starred',
        'read_at',
        'attachment',
        'category',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * الحصول على المرسل
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * الحصول على المستلم
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * تحديث حالة الرسالة إلى مقروءة
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->is_read = true;
            $this->read_at = now();
            $this->save();
        }
        
        return $this;
    }

    /**
     * تبديل حالة التمييز بنجمة
     */
    public function toggleStar()
    {
        $this->is_starred = !$this->is_starred;
        $this->save();
        
        return $this;
    }
}
