<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorWorkingHours extends Model
{
    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_working',
    ];

    protected $casts = [
        'is_working' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public static function getDayName(int $day): string
    {
        return match ($day) {
            1 => 'Bazar ertəsi',
            2 => 'Çərşənbə axşamı',
            3 => 'Çərşənbə',
            4 => 'Cümə axşamı',
            5 => 'Cümə',
            6 => 'Şənbə',
            7 => 'Bazar',
            default => '',
        };
    }
}
