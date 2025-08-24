<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model {
    use HasFactory;

    protected $fillable = [
        'resident_id', 'from_date', 'to_date', 'reason', 'hod_status', 'admin_status'
    ];

    // Relationship with Resident
    public function resident() {
        return $this->belongsTo(Resident::class, 'resident_id', 'id');
    }
}

