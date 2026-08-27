<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * The tenant every account belongs to.
 *
 * A solo specialist is a clinic with a single member — there is no separate
 * "single user" mode, which keeps billing, scoping and permissions uniform.
 */
class Clinic extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'map_url',
        'map_code',
        'notify_channel',
        'sms_appointment_template',
        'sms_reminder_template',
        'sms_birthday_template',
        'sms_copy_to_self',
        'birthday_greetings_enabled',
        'holiday_greetings_enabled',
        'dental_chart_enabled',
        'owner_id',
        'is_active',
    ];

    protected $casts = [
        'sms_copy_to_self'           => 'boolean',
        'is_active'                  => 'boolean',
        'birthday_greetings_enabled' => 'boolean',
        'holiday_greetings_enabled'  => 'boolean',
        'dental_chart_enabled'       => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Every account belonging to this clinic — each one is a paid seat. */
    public function members()
    {
        return $this->hasMany(User::class);
    }

    /** Members who have a calendar and can be assigned appointments. */
    public function specialists()
    {
        return $this->hasMany(User::class)
            ->where('takes_appointments', true)
            ->where('is_active', true);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function treatmentTypes()
    {
        return $this->hasMany(TreatmentType::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(DoctorSubscription::class);
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    /** Dates this clinic added itself, on top of the platform-wide list. */
    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }

    /** Per-holiday overrides for the platform-wide list. */
    public function holidaySettings()
    {
        return $this->hasMany(ClinicHolidaySetting::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(DoctorSubscription::class)
            ->where('is_active', true)
            ->where('expires_at', '>=', now()->toDateString());
    }

    // -------------------------------------------------------------------------
    // Seats
    // -------------------------------------------------------------------------

    /** Accounts currently occupying a seat. */
    public function usedSeats(): int
    {
        return $this->members()->where('is_active', true)->count();
    }

    /** Seats paid for in the current billing period. */
    public function paidSeats(): int
    {
        return (int) ($this->activeSubscription?->seats ?? 0);
    }

    public function remainingSeats(): int
    {
        return max(0, $this->paidSeats() - $this->usedSeats());
    }

    public function canAddMember(): bool
    {
        return $this->remainingSeats() > 0;
    }

    /** True once the clinic has more accounts than it pays for. */
    public function isOverSeatLimit(): bool
    {
        return $this->usedSeats() > $this->paidSeats();
    }

    public function isSolo(): bool
    {
        return $this->usedSeats() <= 1;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Public short link used by the {xerite} message placeholder. */
    public function mapLink(): ?string
    {
        if (! $this->map_code || ! $this->map_url) {
            return null;
        }

        return rtrim(config('app.url'), '/') . '/map/' . $this->map_code;
    }

    public static function generateMapCode(): string
    {
        do {
            $code = Str::lower(Str::random(7));
        } while (static::where('map_code', $code)->exists());

        return $code;
    }
}
