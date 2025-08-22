<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrievanceResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'grievance_id',
        'responded_by',
        'description',
    ];

    // Define the relationship with the Grievance model
    public function grievance()
    {
        return $this->belongsTo(Grievance::class);
    }

    // Define the relationship with the User model (for the responded_by user)
    public function respondedBy()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }


    
}
