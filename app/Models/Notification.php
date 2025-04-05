<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'sender_id',
        'receiver_id',
        'receiver_type',
        'read_at',
        'group_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the sender of the notification.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the notification if it's a user.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the group if the notification is for a group.
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * Check if the notification is read.
     */
    public function isRead()
    {
        return $this->read_at !== null;
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        if (!$this->read_at) {
            $this->read_at = now();
            $this->save();
        }
        
        return $this;
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to only include notifications for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('receiver_id', $userId)
                     ->where('receiver_type', 'user');
    }

    /**
     * Scope a query to only include notifications for a specific group.
     */
    public function scopeForGroup($query, $groupId)
    {
        return $query->where('group_id', $groupId)
                     ->where('receiver_type', 'group');
    }
    
    /**
     * Scope a query to only include notifications for a specific role.
     */
    public function scopeForRole($query, $role)
    {
        return $query->where('receiver_type', $role);
    }
}
