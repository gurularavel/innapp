<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialtyField extends Model
{
    protected $fillable = [
        'specialty_id', 'field_key', 'label', 'type',
        'options', 'is_active', 'is_core', 'sort_order',
    ];

    protected $casts = [
        'options'   => 'array',
        'is_active' => 'boolean',
        'is_core'   => 'boolean',
    ];

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function values()
    {
        return $this->hasMany(PatientFieldValue::class);
    }

    // Built-in patient table columns with their defaults
    public static function coreFields(): array
    {
        return [
            'photo' => [
                'label'      => 'Profil şəkli',
                'type'       => 'photo',
                'options'    => null,
                'col_class'  => 'col-12',
                'sort_order' => 5,
            ],
            'birth_date' => [
                'label'      => 'Doğum tarixi',
                'type'       => 'date',
                'options'    => null,
                'col_class'  => 'col-md-6',
                'sort_order' => 10,
            ],
            'gender' => [
                'label'   => 'Cins',
                'type'    => 'select',
                'options' => [
                    ['value' => 'male',   'label' => 'Kişi'],
                    ['value' => 'female', 'label' => 'Qadın'],
                    ['value' => 'other',  'label' => 'Digər'],
                ],
                'col_class'  => 'col-md-6',
                'sort_order' => 20,
            ],
            'weight' => [
                'label'      => 'Çəki (kg)',
                'type'       => 'number',
                'options'    => null,
                'col_class'  => 'col-md-4',
                'sort_order' => 30,
            ],
            'blood_type' => [
                'label'   => 'Qan qrupu',
                'type'    => 'select',
                'options' => [
                    ['value' => 'A+',  'label' => 'A+'],
                    ['value' => 'A-',  'label' => 'A-'],
                    ['value' => 'B+',  'label' => 'B+'],
                    ['value' => 'B-',  'label' => 'B-'],
                    ['value' => 'AB+', 'label' => 'AB+'],
                    ['value' => 'AB-', 'label' => 'AB-'],
                    ['value' => 'O+',  'label' => 'O+'],
                    ['value' => 'O-',  'label' => 'O-'],
                ],
                'col_class'  => 'col-md-4',
                'sort_order' => 40,
            ],
            'marital_status' => [
                'label'   => 'Ailə vəziyyəti',
                'type'    => 'select',
                'options' => [
                    ['value' => 'single',   'label' => 'Subay'],
                    ['value' => 'married',  'label' => 'Evli'],
                    ['value' => 'divorced', 'label' => 'Boşanmış'],
                    ['value' => 'widowed',  'label' => 'Dul'],
                ],
                'col_class'  => 'col-md-4',
                'sort_order' => 50,
            ],
            'notes' => [
                'label'      => 'Qeydlər',
                'type'       => 'textarea',
                'options'    => null,
                'col_class'  => 'col-12',
                'sort_order' => 60,
            ],
        ];
    }

    // col class for custom fields based on type
    public function getColClassAttribute(): string
    {
        $coreFields = self::coreFields();
        if ($this->is_core && isset($coreFields[$this->field_key])) {
            return $coreFields[$this->field_key]['col_class'];
        }

        return match($this->type) {
            'textarea' => 'col-12',
            default    => 'col-md-6',
        };
    }

    public function isFileType(): bool
    {
        return $this->type === 'file';
    }
}
