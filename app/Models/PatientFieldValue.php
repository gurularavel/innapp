<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientFieldValue extends Model
{
    protected $fillable = ['patient_id', 'specialty_field_id', 'value'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function specialtyField()
    {
        return $this->belongsTo(SpecialtyField::class);
    }
}
