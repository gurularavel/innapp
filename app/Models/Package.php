<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'price_per_seat',
        'min_seats',
        'max_seats',
        'description',
        'patient_limit',
        'duration_days',
        'is_active',
    ];

    protected $casts = [
        'price_per_seat' => 'decimal:2',
        'min_seats'      => 'integer',
        'max_seats'      => 'integer',
        'patient_limit'  => 'integer',
        'duration_days'  => 'integer',
        'is_active'      => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(DoctorSubscription::class);
    }

    /** Monthly price for the given number of staff seats. */
    public function priceFor(int $seats): float
    {
        return round((float) $this->price_per_seat * max($seats, $this->min_seats), 2);
    }

    public function allowsSeats(int $seats): bool
    {
        if ($seats < $this->min_seats) {
            return false;
        }

        return $this->max_seats === null || $seats <= $this->max_seats;
    }

    public function getSeatLabelAttribute(): string
    {
        return number_format((float) $this->price_per_seat, 2) . ' ₼ / əməkdaş';
    }
}
