<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeException extends Model
{
    protected $fillable = [
        'guest_id',
        'hostel_fee',
        'caution_money',
        'total_amount',
        'facility',
        'approved_by',
        'remarks',
        'document_path',
        'created_by',
        'account_remark',
        'start_date',
        'end_date'
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
