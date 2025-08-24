<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;
    protected $table = 'faculties';

    protected $fillable = [
        'name',
    ];
    
    public function university()
    {
        return $this->belongsTo(University::class, 'university_id');
    }
    // public function departments()
    // {
    //     return $this->hasMany(Department::class, 'faculty_id');
    // }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
