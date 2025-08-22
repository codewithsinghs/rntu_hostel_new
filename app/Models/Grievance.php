<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Grievance extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'created_by',
        'responded_by',
        'type_of_complaint',
        'description',
        'status',
        'token_id',
        'photo'
    ];

    // Define relationships

    /**
     * Get the resident that owns the grievance.
     */
    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * Get the user who created the grievance (Admin).
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors and Mutators (if needed)

    /**
     * Automatically generate a token for the grievance.
     */
    protected static function booted()
    {
        static::creating(function ($grievance) {
            if (!$grievance->token_id) {
                $grievance->token_id = \Str::uuid(); // Generate unique token for each grievance
            }
        });
    }

    public function respondedBy()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }


    public function responses()
    {
        return $this->hasMany(GrievanceResponse::class);
    }
}
