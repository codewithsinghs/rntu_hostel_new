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
        'name',
        'email',
        'gender',
        'scholar_no',
        'fathers_name',
        'mothers_name',
        'local_guardian_name',
        'emergency_no',
        'number',
        'parent_no',
        'guardian_no',
        'room_preference',
        'food_preference',
        'fee_waiver',
        'remarks',
        'status',
        'months',
        'attachment_path',
        'days',
        'admin_remarks'
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

    
}
