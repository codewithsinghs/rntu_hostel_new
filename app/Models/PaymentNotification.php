<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentNotification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * These fields can be set during mass assignment (e.g., PaymentNotification::create([...])).
     * 'is_read' is included here, defaulting to false in the database.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_id',
        'resident_id',
        'notification_type',
        'sms_gateway_message_id',
        'sent_at',
        'is_read', 
    ];

    /**
     * Define the relationship: A payment notification belongs to a Payment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Define the relationship: A payment notification belongs to a Resident.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}

