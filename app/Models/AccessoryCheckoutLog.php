<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessoryCheckoutLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'checkout_id',
        'accessory_head_id',
        'is_returned',
        'debit_amount',
        'remark',
    ];

    // Relationships
    public function checkout()
    {
        return $this->belongsTo(Checkout::class);
    }

    public function accessory()
    {
        return $this->belongsTo(Accessory::class, 'accessory_head_id');
    }
}
