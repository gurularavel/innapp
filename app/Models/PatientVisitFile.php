<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    protected static function booted(): void
    {
        static::deleting(function (self $file) {
            Storage::disk('public')->delete($file->file_path);
        });
    }
}
