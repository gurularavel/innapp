<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientVisit extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'visited_at',
        'title',
        'notes',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function files()
    {
        return $this->hasMany(PatientVisitFile::class);
    }
}
