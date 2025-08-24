<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mess extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'resident_id',
        'guest_id',
        'building_id',
        'university_id',
        'created_by',
        'food_preference',
        'from_date',
        'to_date',
        'due_date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
