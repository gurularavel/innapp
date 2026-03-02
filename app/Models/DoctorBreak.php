<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorBreak extends Model
{
    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'label',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
