<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomChangeMessage extends Model
{
    protected $fillable = [
        'room_change_request_id',
        'sender',
        'message',
        'created_by',
    ];

    public function roomChangeRequest()
    {
        return $this->belongsTo(RoomChangeRequest::class);
    }
}
