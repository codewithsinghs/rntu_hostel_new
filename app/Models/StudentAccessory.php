<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAccessory extends Model
{
    use HasFactory;

    protected $table = 'student_accessory';

    protected $fillable = ['resident_id', 'accessory_head_id', 'price', 'total_amount', 'from_date', 'to_date','due_date'];

    // Relationship with Resident
    public function resident() {
        return $this->belongsTo(Resident::class);
    }

    // Relationship with Accessory
    public function accessory() {
        return $this->belongsTo(Accessory::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'student_accessory_id');
    }

    public function accessoryHead()
{
    return $this->belongsTo(AccessoryHead::class, 'accessory_head_id');
}

}


