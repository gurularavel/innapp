<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'treatment_type_id',
        'scheduled_at',
        'duration_minutes',
        'status',
        'notes',
        'reminder_sent',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'reminder_sent' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function treatmentType()
    {
        return $this->belongsTo(TreatmentType::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    public function getEndTimeAttribute()
    {
        return (clone $this->scheduled_at)->addMinutes($this->duration_minutes);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Gözləyir',
            'confirmed' => 'Təsdiqləndi',
            'completed' => 'Tamamlandı',
            'cancelled' => 'Ləğv edildi',
            default => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}
