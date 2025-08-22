<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model {
    
    use HasFactory;
    protected $table = 'feedbacks';
    protected $fillable = ['resident_id', 'facility_name', 'feedback', 'suggestion','photo_path'];

    // Define relationship with Resident model
    public function resident() {
        return $this->belongsTo(Resident::class, 'resident_id');
    }
}
