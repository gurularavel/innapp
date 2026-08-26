<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'appointment_id',
        'patient_id',
        'clinic_id',
        'doctor_id',
        'phone',
        'message',
        'type',
        'reference',
        'channel',
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

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /** Set for greetings, which are addressed to a person rather than a booking. */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /** The member on whose behalf the message went out. */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'appointment' => 'Randevu',
            'reminder'    => 'Xatırlatma',
            'birthday'    => 'Ad günü',
            'holiday'     => 'Bayram',
            default       => 'Digər',
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'appointment' => 'primary',
            'reminder'    => 'info',
            'birthday'    => 'warning',
            'holiday'     => 'success',
            default       => 'secondary',
        };
    }

    public function getChannelLabelAttribute(): string
    {
        return $this->channel === 'whatsapp' ? 'WhatsApp' : 'SMS';
    }

    public function getChannelBadgeAttribute(): string
    {
        return $this->channel === 'whatsapp' ? 'success' : 'primary';
    }

    public function getChannelIconAttribute(): string
    {
        return $this->channel === 'whatsapp' ? 'bi-whatsapp' : 'bi-chat-text';
    }
}
