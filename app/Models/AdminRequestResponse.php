<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminRequestResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_request_id',
        'user_id',
        'content'
    ];

    /**
     * Get the admin request that owns the response.
     */
    public function adminRequest()
    {
        return $this->belongsTo(AdminRequest::class);
    }

    /**
     * Get the user who created the response.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 