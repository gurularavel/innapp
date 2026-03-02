<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
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
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
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
