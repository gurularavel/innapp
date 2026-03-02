<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSubscription extends Model
{
    protected $fillable = [
        'doctor_id',
        'package_id',
        'starts_at',
        'expires_at',
        'patients_used',
        'sms_used',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function patientLimitReached(): bool
    {
        if ($this->package->patient_limit === null) {
            return false;
        }
        return $this->patients_used >= $this->package->patient_limit;
    }

    public function smsLimitReached(): bool
    {
        if ($this->package->sms_limit === null) {
            return false;
        }
        return $this->sms_used >= $this->package->sms_limit;
    }

    public function getRemainingPatientsAttribute(): ?int
    {
        if ($this->package->patient_limit === null) {
            return null;
        }
        return max(0, $this->package->patient_limit - $this->patients_used);
    }

    public function getRemainingSmsAttribute(): ?int
    {
        if ($this->package->sms_limit === null) {
            return null;
        }
        return max(0, $this->package->sms_limit - $this->sms_used);
    }
}
