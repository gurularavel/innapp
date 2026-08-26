<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A clinic's subscription for a billing period.
 *
 * `seats` is what the clinic paid for; the clinic cannot have more active
 * accounts than seats. Messaging is unlimited, so no message counter here.
 */
class DoctorSubscription extends Model
{
    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'package_id',
        'seats',
        'price_per_seat',
        'starts_at',
        'expires_at',
        'patients_used',
        'is_active',
    ];

    protected $casts = [
        'starts_at'      => 'date',
        'expires_at'     => 'date',
        'seats'          => 'integer',
        'price_per_seat' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /** The member who paid — kept for the payment history. */
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

    /** Total paid for this period. */
    public function getTotalPriceAttribute(): float
    {
        return round((float) $this->price_per_seat * $this->seats, 2);
    }

    public function getUsedSeatsAttribute(): int
    {
        return $this->clinic?->usedSeats() ?? 0;
    }

    public function getRemainingSeatsAttribute(): int
    {
        return max(0, $this->seats - $this->used_seats);
    }

    public function seatsExceeded(): bool
    {
        return $this->used_seats > $this->seats;
    }

    public function patientLimitReached(): bool
    {
        if ($this->package->patient_limit === null) {
            return false;
        }

        return $this->patients_used >= $this->package->patient_limit;
    }

    public function getRemainingPatientsAttribute(): ?int
    {
        if ($this->package->patient_limit === null) {
            return null;
        }

        return max(0, $this->package->patient_limit - $this->patients_used);
    }
}
