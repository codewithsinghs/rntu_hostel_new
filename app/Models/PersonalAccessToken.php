<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

class PersonalAccessToken extends SanctumToken
{
    protected $hidden = [
        'tokenable', // prevent recursive loading
        'abilities',
        'last_used_at',
        'created_at',
        'updated_at',
    ];
}
