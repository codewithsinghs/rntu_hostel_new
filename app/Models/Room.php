<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    
    protected $fillable = ['room_number', 'building_id', 'floor_no', 'status']; // Added floor_no & status

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }
}

