<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_key',
        'private_key',
        'owner',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}

