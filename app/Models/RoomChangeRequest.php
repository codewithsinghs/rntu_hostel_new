<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'reason',
        'preference',
        'action',
        'remark',
        'resident_agree',
        'created_by', 
        'token',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    public function Bed()
    {
        return $this->belongsTo(Bed::class);
    }
}
