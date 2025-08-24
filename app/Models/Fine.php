<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'rule_breach_description',
        'admin_proposed_amount',
        'final_amount',
        'admin_remark',
        'accountant_remark',
        'status',
        'assigned_by_admin_id',
        'approved_by_accountant_id',
    ];

    protected $casts = [
        'admin_proposed_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function assignedByAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_by_admin_id');
    }

    public function approvedByAccountant()
    {
        return $this->belongsTo(User::class, 'approved_by_accountant_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'fine_id');
    }
}