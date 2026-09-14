<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** In-clinic roles, ordered by authority. */
    public const CLINIC_ROLES = ['owner', 'doctor', 'receptionist'];

    protected $fillable = [
        'clinic_id',
        'name',
        'surname',
        'email',
        'phone',
        'password',
        'role',
        'specialty_id',
        'takes_appointments',
        'job_title',
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
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'is_active'          => 'boolean',
            'takes_appointments' => 'boolean',
            'is_demo'            => 'boolean',
            'demo_expires_at'    => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Roles
    // -------------------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isPromoter(): bool
    {
        return $this->role === 'promoter';
    }

    /** Belongs to a clinic — owner, specialist or receptionist. */
    public function isClinicMember(): bool
    {
        return in_array($this->role, self::CLINIC_ROLES, true);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    /**
     * Kept for backwards compatibility: anywhere that used to ask "is this a
     * doctor?" really means "is this a clinic member?".
     */
    public function isDoctor(): bool
    {
        return $this->isClinicMember();
    }

    /** URL prefix of the panel this role lives in ("/admin", "/panel", "/promoter"). */
    public function panelPrefix(): string
    {
        return match (true) {
            $this->isAdmin()    => '/admin',
            $this->isPromoter() => '/promoter',
            default             => '/panel',
        };
    }

    /** Landing page of the user's own panel. */
    public function homeUrl(): string
    {
        return match (true) {
            $this->isAdmin()    => route('admin.dashboard'),
            $this->isPromoter() => route('promoter.dashboard'),
            default             => route('panel.dashboard'),
        };
    }

    /**
     * Whether a URL belongs to this user's own panel. Used to decide if a
     * remembered "intended" URL may be honoured after login — a URL left
     * behind by a previous account of another role must not be.
     */
    public function ownsUrl(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        $path = '/' . ltrim((string) parse_url($url, PHP_URL_PATH), '/');
        $prefix = $this->panelPrefix();

        return $path === $prefix || str_starts_with($path, $prefix . '/');
    }

    /** Only the owner manages staff, billing and clinic-wide settings. */
    public function canManageClinic(): bool
    {
        return $this->isOwner();
    }

    /** Has a calendar and can be assigned appointments. */
    public function takesAppointments(): bool
    {
        return $this->takes_appointments && $this->isClinicMember();
    }

    /**
     * Every account that belongs to a clinic — owner, specialist or
     * receptionist. Registration creates owners, so an admin listing that
     * filters on `doctor` alone would show nobody.
     */
    public function scopeStaff($query)
    {
        return $query->whereIn('role', self::CLINIC_ROLES);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin'  => 'Sistem admini',
            'owner'        => 'Klinika sahibi',
            'doctor'       => 'Mütəxəssis',
            'receptionist' => 'Resepsiyonist',
            'promoter'     => 'Promotor',
            default        => $this->role,
        };
    }

    // -------------------------------------------------------------------------
    // Clinic scoping
    // -------------------------------------------------------------------------

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /** Colleagues in the same clinic, including this user. */
    public function clinicMembers()
    {
        return $this->hasMany(User::class, 'clinic_id', 'clinic_id');
    }

    /** The clinic's shared patient base — every member sees the same records. */
    public function patients()
    {
        return $this->hasMany(Patient::class, 'clinic_id', 'clinic_id');
    }

    /** Services are defined once for the whole clinic. */
    public function treatmentTypes()
    {
        return $this->hasMany(TreatmentType::class, 'clinic_id', 'clinic_id');
    }

    /** This member's own appointments — their personal calendar. */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    /** Every appointment in the clinic, regardless of who takes it. */
    public function clinicAppointments()
    {
        return $this->hasMany(Appointment::class, 'clinic_id', 'clinic_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(DoctorSubscription::class, 'clinic_id', 'clinic_id');
    }

    public function activeSubscription()
    {
        return $this->hasOne(DoctorSubscription::class, 'clinic_id', 'clinic_id')
            ->where('is_active', true)
            ->where('expires_at', '>=', now()->toDateString());
    }

    public function workingHours()
    {
        return $this->hasMany(DoctorWorkingHours::class, 'doctor_id');
    }

    public function breaks()
    {
        return $this->hasMany(DoctorBreak::class, 'doctor_id');
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    // -------------------------------------------------------------------------
    // Promoter
    // -------------------------------------------------------------------------

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
     * Promoter commission balances (AZN).
     * pending   = on hold
     * available = withdrawable
     * paid      = already paid out
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

    public function getFullNameAttribute(): string
    {
        return trim($this->name . ' ' . $this->surname);
    }
}
