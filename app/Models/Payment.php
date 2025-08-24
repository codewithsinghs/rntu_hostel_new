<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'guest_id',
        'resident_id',
        'fee_head_id',
        'amount',
        'total_amount',
        'remaining_amount',
        'transaction_id',
        'payment_method',
        'payment_status',
        'created_by',
        'remarks',
        'subscription_id',
        'student_accessory_id',
        'due_date',
        'is_caution_money'
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function feeHead()
    {
        return $this->belongsTo(Fee::class, 'fee_head_id');
    }
public function fees()
{
    return $this->belongsTo(Fee::class, 'fee_head_id');
}

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function studentAccessory()
    {
        return $this->belongsTo(StudentAccessory::class, 'student_accessory_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


}
