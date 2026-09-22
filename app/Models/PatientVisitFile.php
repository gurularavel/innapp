<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\PatientFiles;

class PatientVisitFile extends Model
{
    protected $fillable = [
        'patient_visit_id',
        'file_path',
        'original_name',
        'mime_type',
    ];

    public function visit()
    {
        return $this->belongsTo(PatientVisit::class, 'patient_visit_id');
    }

    /** Authenticated link — the file itself is on the private disk. */
    public function getUrlAttribute(): string
    {
        return route('panel.files.visit', $this);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    protected static function booted(): void
    {
        static::deleting(function (self $file) {
            PatientFiles::delete($file->file_path);
        });
    }
}
