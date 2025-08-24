<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable; // ✅ this is important
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Guest extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'scholar_number',
        'name',
        'email',
        'mobile',
        'password',
        'gender',
        'fathers_name',
        'mothers_name',
        'parent_contact',
        'emergency_contact',
        'local_guardian_name',
        'guardian_contact',
        'room_preference',
        'food_preference',
        'months',
        'days',
        'fee_waiver',
        'attachment_path',
        'remarks',
        'admin_remarks',
        'token',
        'token_expiry',
        'status',
        'created_by',

    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'remember_token',
        // add other sensitive or recursive fields
        ];
    
    
        public function accessory()
        {
            return $this->belongsToMany(Accessory::class, 'guest_accessory', 'guest_id', 'accessory_id');
        }
        public function accessories()
        {
            return $this->belongsToMany(Accessory::class, 'guest_accessory', 'guest_id', 'accessory_head_id')
                ->withPivot(['price', 'total_amount', 'from_date', 'to_date'])
                ->with('accessoryHead');
        }
    
    
        public function feeException()
        {
            return $this->hasOne(FeeException::class);
        }


    // public function accessories()
    // {
    //     return $this->hasMany(GuestAccessory::class);
    // }


    // public function feeException()
    // {
    //     return $this->hasOne(FeeException::class);
    // }
}
