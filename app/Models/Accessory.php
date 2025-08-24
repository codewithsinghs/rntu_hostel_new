<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accessory extends Model
{
    protected $table = 'accessories';

    
    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];
    

    protected $fillable = [
        'accessory_head_id',
        'price',
        'is_default',
        'from_date',
        'to_date',
        'is_active',
        'created_by',
    ];

    // Relationship: Accessory belongs to AccessoryHead
    public function accessoryHead()
    {
        return $this->belongsTo(AccessoryHead::class, 'accessory_head_id');
    }

    // (Optional) created_by user relationship if needed
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accessory()
    {
        return $this->hasMany(GuestAccessory::class);
    } 
}
