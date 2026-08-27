<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One tooth marked on a visit, in FDI two-digit notation.
 *
 * First digit = quadrant (1 upper-right … 4 lower-left for permanent teeth,
 * 5 … 8 for the primary set), second digit = position from the midline.
 */
class PatientVisitTooth extends Model
{
    protected $table = 'patient_visit_teeth';

    protected $fillable = [
        'patient_visit_id',
        'tooth_number',
        'status',
        'note',
    ];

    protected $casts = [
        'tooth_number' => 'integer',
    ];

    /** What can be marked on a tooth, in the order the palette shows it. */
    public const STATUSES = [
        'treated'   => ['label' => 'Müalicə olundu', 'color' => '#198754', 'icon' => 'bi-check-circle'],
        'caries'    => ['label' => 'Karies',         'color' => '#dc3545', 'icon' => 'bi-bug'],
        'filling'   => ['label' => 'Plomb',          'color' => '#0d6efd', 'icon' => 'bi-circle-half'],
        'root_canal'=> ['label' => 'Kanal müalicəsi','color' => '#6f42c1', 'icon' => 'bi-diagram-2'],
        'crown'     => ['label' => 'Qapaq / Protez', 'color' => '#fd7e14', 'icon' => 'bi-gem'],
        'implant'   => ['label' => 'İmplant',        'color' => '#20c997', 'icon' => 'bi-nut'],
        'extraction'=> ['label' => 'Çəkilib',        'color' => '#6c757d', 'icon' => 'bi-x-circle'],
        'planned'   => ['label' => 'Planlanır',      'color' => '#0dcaf0', 'icon' => 'bi-clock'],
    ];

    public const DEFAULT_STATUS = 'treated';

    /** Left-to-right order of each arch row, as the chart draws it. */
    public const LAYOUT = [
        'permanent' => [
            'upper' => [[18, 17, 16, 15, 14, 13, 12, 11], [21, 22, 23, 24, 25, 26, 27, 28]],
            'lower' => [[48, 47, 46, 45, 44, 43, 42, 41], [31, 32, 33, 34, 35, 36, 37, 38]],
        ],
        'primary' => [
            'upper' => [[55, 54, 53, 52, 51], [61, 62, 63, 64, 65]],
            'lower' => [[85, 84, 83, 82, 81], [71, 72, 73, 74, 75]],
        ],
    ];

    private const QUADRANT_LABELS = [
        1 => 'Yuxarı sağ', 2 => 'Yuxarı sol', 3 => 'Aşağı sol', 4 => 'Aşağı sağ',
        5 => 'Yuxarı sağ (süd)', 6 => 'Yuxarı sol (süd)', 7 => 'Aşağı sol (süd)', 8 => 'Aşağı sağ (süd)',
    ];

    // -------------------------------------------------------------------------

    public function visit()
    {
        return $this->belongsTo(PatientVisit::class, 'patient_visit_id');
    }

    // -------------------------------------------------------------------------
    // FDI helpers
    // -------------------------------------------------------------------------

    /** Every tooth number the chart can draw — the whitelist for incoming data. */
    public static function allNumbers(): array
    {
        static $numbers;

        if ($numbers === null) {
            $numbers = [];
            foreach (self::LAYOUT as $arches) {
                foreach ($arches as $rows) {
                    foreach ($rows as $row) {
                        $numbers = array_merge($numbers, $row);
                    }
                }
            }
            sort($numbers);
        }

        return $numbers;
    }

    public static function isValidNumber(int $number): bool
    {
        return in_array($number, self::allNumbers(), true);
    }

    /** "Yuxarı sağ", "Aşağı sol (süd)" … */
    public static function quadrantLabel(int $number): string
    {
        return self::QUADRANT_LABELS[intdiv($number, 10)] ?? '';
    }

    public static function jawLabel(int $number): string
    {
        return in_array(intdiv($number, 10), [1, 2, 5, 6], true) ? 'Yuxarı çənə' : 'Aşağı çənə';
    }

    /** Which silhouette to draw: incisor / canine / premolar / molar. */
    public static function shape(int $number): string
    {
        $position = $number % 10;
        $isPrimary = intdiv($number, 10) >= 5;

        return match (true) {
            $position <= 2 => 'incisor',
            $position === 3 => 'canine',
            $isPrimary     => 'molar',      // primary sets have no premolars
            $position <= 5 => 'premolar',
            default        => 'molar',
        };
    }

    public static function statusLabel(?string $status): string
    {
        return self::STATUSES[$status]['label'] ?? 'Qeyd olunub';
    }

    public static function statusColor(?string $status): string
    {
        return self::STATUSES[$status]['color'] ?? '#6c757d';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabel($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::statusColor($this->status);
    }

    /** "16 — Yuxarı sağ" */
    public function getDisplayNameAttribute(): string
    {
        return $this->tooth_number . ' — ' . self::quadrantLabel($this->tooth_number);
    }
}
