<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'price',
        'patient_limit',
        'sms_limit',
        'duration_days',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function subscriptions()
    {
        return $this->hasMany(DoctorSubscription::class);
    }
}
