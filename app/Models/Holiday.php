<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A date greetings go out on.
 *
 * Rows without a `clinic_id` are the platform-wide list the admin curates;
 * every clinic sees them and may switch each one off or reword it. Rows with a
 * `clinic_id` belong to that clinic alone.
 */
class Holiday extends Model
{
    public const MONTHS = [
        1 => 'Yanvar',  2 => 'Fevral',  3 => 'Mart',    4 => 'Aprel',
        5 => 'May',     6 => 'İyun',    7 => 'İyul',    8 => 'Avqust',
        9 => 'Sentyabr', 10 => 'Oktyabr', 11 => 'Noyabr', 12 => 'Dekabr',
    ];

    protected $fillable = [
        'clinic_id',
        'name',
        'month',
        'day',
        'year',
        'template',
        'is_active',
    ];

    protected $casts = [
        'month'     => 'integer',
        'day'       => 'integer',
        'year'      => 'integer',
        'is_active' => 'boolean',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function clinicSettings()
    {
        return $this->hasMany(ClinicHolidaySetting::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** The platform-wide list every clinic can draw on. */
    public function scopeShared(Builder $query): Builder
    {
        return $query->whereNull('clinic_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Holidays landing on this date — the ones that recur every year plus any
     * moving date pinned to this particular year.
     */
    public function scopeOnDate(Builder $query, CarbonInterface $date): Builder
    {
        return $query
            ->where('month', $date->month)
            ->where('day', $date->day)
            ->where(fn (Builder $q) => $q->whereNull('year')->orWhere('year', $date->year));
    }

    /** Chronological within the year, so the admin list reads like a calendar. */
    public function scopeInCalendarOrder(Builder $query): Builder
    {
        return $query->orderBy('month')->orderBy('day');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isShared(): bool
    {
        return $this->clinic_id === null;
    }

    /** True for a moving date that was pinned to one year only. */
    public function isOneOff(): bool
    {
        return $this->year !== null;
    }

    public function getDateLabelAttribute(): string
    {
        $label = $this->day . ' ' . (self::MONTHS[$this->month] ?? $this->month);

        return $this->year ? $label . ' ' . $this->year : $label;
    }

    /** Whether this row can still fire — a past one-off never will again. */
    public function hasPassed(): bool
    {
        if (! $this->isOneOff()) {
            return false;
        }

        return $this->year < now()->year
            || ($this->year === now()->year
                && ($this->month < now()->month || ($this->month === now()->month && $this->day < now()->day)));
    }
}
