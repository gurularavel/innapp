<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'sms_appointment_template',
        'sms_reminder_template',
        'muessise_adi',
        'sms_copy_to_self',
        'password',
        'role',
        'specialty_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'sms_copy_to_self' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'doctor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function treatmentTypes()
    {
        return $this->hasMany(TreatmentType::class, 'doctor_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(DoctorSubscription::class, 'doctor_id');
    }

    public function workingHours()
    {
        return $this->hasMany(DoctorWorkingHours::class, 'doctor_id');
    }

    public function breaks()
    {
        return $this->hasMany(DoctorBreak::class, 'doctor_id');
    }

    public function activeSubscription()
    {
        return $this->hasOne(DoctorSubscription::class, 'doctor_id')
            ->where('is_active', true)
            ->where('expires_at', '>=', now()->toDateString());
    }

    public function getFullNameAttribute(): string
    {
        return $this->name . ' ' . $this->surname;
    }
}
