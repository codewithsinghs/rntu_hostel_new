<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory;
    protected $fillable = ['bed_number', 'room_id', 'status'];

    public function resident()
    {
        return $this->hasOne(Resident::class, 'bed_id', 'id'); // One bed has only one resident
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function accessories()
    {
        return $this->belongsToMany(Accessory::class, 'student_accessories')
            ->withPivot('price');
    }
}
