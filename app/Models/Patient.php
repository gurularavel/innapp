<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'name',
        'surname',
        'phone',
        'birth_date',
        'gender',
        'weight',
        'blood_type',
        'marital_status',
        'notes',
        'photo',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /** The member who registered this patient. */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function visits()
    {
        return $this->hasMany(PatientVisit::class)->latest('visited_at');
    }

    public function fieldValues()
    {
        return $this->hasMany(PatientFieldValue::class);
    }

    /** Authenticated link to the photo — the file itself is on the private disk. */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && \App\Support\PatientFiles::exists($this->photo)) {
            return route('panel.files.photo', $this);
        }
        return '';
    }

    public function getFullNameAttribute(): string
    {
        return $this->name . ' ' . $this->surname;
    }

    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }
        return Carbon::parse($this->birth_date)->diffInYears(now());
    }

    public function getMaritalStatusLabelAttribute(): ?string
    {
        return match ($this->marital_status) {
            'single'   => 'Subay',
            'married'  => 'Evli',
            'divorced' => 'Boşanmış',
            'widowed'  => 'Dul',
            default    => null,
        };
    }
}
