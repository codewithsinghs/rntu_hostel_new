<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveNotification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'leave_request_id',
        'resident_id',
        'notification_type',
        'sms_gateway_message_id',
        'sent_at',
        'is_read',
    ];

    /**
     * Get the leave request that owns the notification.
     */
    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    /**
     * Get the resident associated with the notification.
     */
    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}

