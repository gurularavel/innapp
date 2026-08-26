<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One clinic's stance on one platform-wide holiday.
 *
 * A missing row means the clinic has not decided yet and the holiday's own
 * defaults apply, so nothing needs to be pre-created for every clinic.
 */
class ClinicHolidaySetting extends Model
{
    protected $fillable = [
        'clinic_id',
        'holiday_id',
        'is_enabled',
        'template',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function holiday()
    {
        return $this->belongsTo(Holiday::class);
    }
}
