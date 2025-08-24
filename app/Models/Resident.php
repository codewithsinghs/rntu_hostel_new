<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        
            'name',
            'email',
            'gender',
            'scholar_number',
            'number',
            'parent_no',
            'guardian_no',
            'fathers_name',
            'mothers_name',
            'user_id',
            'bed_id',
            'status',
            'guest_id',
            'created_by'
        

    ];

    // Relationship with Users table
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'resident_id');
    }

    public function checkouts()
    {
        return $this->hasMany(Checkout::class);
    }
    public function accessories()
    {
        return $this->hasMany(StudentAccessory::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function room()
    {
        return $this->belongsTo(Room::class); // If you have a rooms table
    }
}
