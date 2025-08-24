<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestAccessory extends Model
{
    use HasFactory;

    protected $table = 'guest_accessory';

    protected $fillable = [
        'guest_id',
        'accessory_head_id',
        'price',
        'is_returned',
        'total_amount',
        'from_date',
        'to_date'
    ];

    
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }


    public function accessory()
    {
        return $this->belongsTo(Accessory::class);
    }


    public function accessoryHead()
    {
        return $this->belongsTo(AccessoryHead::class, 'accessory_head_id');
    }


    public function accessories()
    {
        return $this->belongsToMany(Accessory::class, 'guest_accessory', 'guest_id', 'accessory_head_id')
            ->withPivot(['price', 'total_amount', 'from_date', 'to_date']);
    }
}
