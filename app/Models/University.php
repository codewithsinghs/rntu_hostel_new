<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'state',
        'district',
        'pincode',
        'address',
        'mobile',
        'email',
    ];

    /**
     * Define relationship: One University has many Admins (Users)
     */
    public function admins()
    {
        return $this->hasMany(User::class, 'university_id'); // Explicit foreign key
    }

    /**
     * Define relationship: One University has many Buildings
     */
    public function buildings()
    {
        return $this->hasMany(Building::class, 'university_id'); // Explicit foreign key
    }

    public function faculties()
    {
        return $this->hasMany(Faculty::class, 'university_id');
    }

}

