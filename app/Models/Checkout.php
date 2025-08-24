<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'date',
        'reason',
        'deposited_amount',
        'admin_approval',
        'account_approval',
        'remark',
        'action'
    ];

    // Relationship with Resident
    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}
