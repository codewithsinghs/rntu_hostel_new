<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = [
        'name',
        'faculty_id',
    ];

    // public function faculty()
    // {
    //     return $this->belongsTo(Faculty::class, 'faculty_id');
    // }

    //     public function courses()
    // {
    //     return $this->hasMany(Course::class, 'department_id');
    // }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
