<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'university_id', 'building_code','floors', 'status', 'created_by'];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
