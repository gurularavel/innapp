<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'phone',
        'message',
        'type',
        'status',
        'sent_at',
        'receiver_id',
        'response_body',
    ];

    protected $casts = [
        'sent_at'       => 'datetime',
        'response_body' => 'array',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
