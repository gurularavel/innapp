<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientVisit extends Model
{
    protected $fillable = [
        'clinic_id',
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

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /** The member who wrote this visit note. */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function files()
    {
        return $this->hasMany(PatientVisitFile::class);
    }

    /** Teeth marked on this visit (dental clinics only), ordered as FDI numbers read. */
    public function teeth()
    {
        return $this->hasMany(PatientVisitTooth::class)->orderBy('tooth_number');
    }
}
