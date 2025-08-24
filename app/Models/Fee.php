<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'fee_head_id', 'amount', 'from_date', 'to_date', 'is_active', 'created_by'];


    // Define Relationship: A Fee can have multiple payments
    public function payments()
    {
        return $this->hasMany(Payment::class, 'fee_head_id');
    }

    public function feeHead()
    {
        return $this->belongsTo(FeeHead::class);
    }
}
