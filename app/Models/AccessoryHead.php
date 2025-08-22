<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessoryHead extends Model
{
    protected $table = 'accessory_heads';

    use HasFactory;

    protected $fillable = [
        'name',
        'created_by',
    ];

        public function studentAccessories()
    {
        return $this->hasMany(StudentAccessory::class);
    }
}
