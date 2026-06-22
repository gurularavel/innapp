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
        'muessise_unvani',
        'muessise_xerite',
        'muessise_xerite_code',
        'sms_copy_to_self',
        'password',
        'role',
        'specialty_id',
        'is_active',
        'is_demo',
        'demo_expires_at',
        'signup_promo_code_id',
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
            'is_demo' => 'boolean',
            'demo_expires_at' => 'datetime',
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

    public function isPromoter(): bool
    {
        return $this->role === 'promoter';
    }

    public function promoCodes()
    {
        return $this->hasMany(PromoCode::class, 'promoter_id');
    }

    public function signupPromoCode()
    {
        return $this->belongsTo(PromoCode::class, 'signup_promo_code_id');
    }

    public function redemptions()
    {
        return $this->hasMany(PromoRedemption::class, 'promoter_id');
    }

    public function payouts()
    {
        return $this->hasMany(PromoterPayout::class, 'promoter_id');
    }

    /**
     * Promotorun komissiya balansları (AZN).
     * pending  = gözləmədə (hold müddəti bitməyib)
     * available = çıxarıla bilən
     * paid     = artıq ödənilib
     */
    public function commissionBalances(): array
    {
        $sums = $this->redemptions()
            ->selectRaw('status, COALESCE(SUM(commission_amount), 0) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'pending'   => round((float) ($sums['pending'] ?? 0), 2),
            'available' => round((float) ($sums['available'] ?? 0), 2),
            'paid'      => round((float) ($sums['paid'] ?? 0), 2),
        ];
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
