<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessoryHead extends Model
{
    protected $table = 'accessory_heads';

    use HasFactory;
    // Orig
    // protected $fillable = [
    //     'name',
    //     'created_by',
    // ];

    // public function studentAccessories()
    // {
    //     return $this->hasMany(StudentAccessory::class);
    // }

    // protected $fillable = [
    //     'name',
    //     'is_paid',
    //     'default_price',
    //     'billing_cycle',
    //     'is_active',
    // ];

    // public function guests()
    // {
    //     return $this->belongsToMany(Guest::class, 'guest_accessory', 'accessory_head_id', 'guest_id')
    //         ->withPivot([
    //             'price', 'total_amount', 'from_date', 'to_date',
    //             'billing_cycle', 'is_complementary', 'status'
    //         ]);
    // }

    // public function guestAccessories()
    // {
    //     return $this->hasMany(GuestAccessory::class);
    // }

    protected $fillable = [
        'name',
        'created_by',
    ];

        public function studentAccessories()
    {
        return $this->hasMany(StudentAccessory::class);
    }
}
