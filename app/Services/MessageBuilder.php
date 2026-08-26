<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Setting;

/**
 * Builds the outgoing message text for an appointment.
 *
 * Templates live on the clinic, not the individual member, so every message a
 * clinic sends speaks with one voice regardless of which staff member triggered it.
 */
class MessageBuilder
{
    public const PLACEHOLDERS = [
        '{ad_soyad}',
        '{xidmet}',
        '{mutexessis}',
        '{tarix}',
        '{saat}',
        '{muessise}',
        '{xerite}',
    ];

    /** Greetings are addressed to a person, so they use their own placeholder set. */
    public const BIRTHDAY_PLACEHOLDERS = [
        '{ad}',
        '{ad_soyad}',
        '{yas}',
        '{muessise}',
        '{xerite}',
    ];

    public const HOLIDAY_PLACEHOLDERS = [
        '{ad}',
        '{ad_soyad}',
        '{bayram}',
        '{muessise}',
        '{xerite}',
    ];

    private const GLOBAL_DEFAULTS = [
        'sms_appointment_template' => 'Hörmətli {ad_soyad}, {tarix} {saat} randevunuz təsdiqləndi.',
        'sms_reminder_template'    => 'Xatırlatma: {ad_soyad}, {tarix} {saat} randevunuz var.',
        'sms_birthday_template'    => 'Hörmətli {ad_soyad}, ad gününüz mübarək olsun! {muessise}',
        'sms_holiday_template'     => 'Hörmətli {ad_soyad}, {bayram} münasibətilə sizi təbrik edirik! {muessise}',
    ];

    /**
     * Resolve the template for this appointment and fill in the placeholders.
     *
     * Priority: the clinic's own template » the admin default » the hardcoded fallback.
     */
    public function build(string $templateKey, Appointment $appointment): string
    {
        $clinic = $appointment->clinic ?? $appointment->doctor?->clinic;

        $template = ($clinic?->{$templateKey} ?? null)
            ?: Setting::get($templateKey, self::GLOBAL_DEFAULTS[$templateKey] ?? '');

        $values = $this->values($appointment);

        return str_replace(array_keys($values), array_values($values), $template);
    }

    /**
     * Placeholder => resolved value map for this appointment.
     *
     * @return array<string, string>
     */
    public function values(Appointment $appointment): array
    {
        $clinic      = $appointment->clinic ?? $appointment->doctor?->clinic;
        $staff       = $appointment->doctor;
        $patient     = $appointment->patient;
        $scheduledAt = $appointment->scheduled_at;

        return [
            '{ad_soyad}'   => $patient->full_name,
            '{xidmet}'     => $appointment->treatmentType?->name ?? '',
            '{mutexessis}' => $staff?->full_name ?? '',
            '{tarix}'      => $scheduledAt->format('d.m.Y'),
            '{saat}'       => $scheduledAt->format('H:i'),
            '{muessise}'   => $clinic?->name ?: (string) Setting::get('default_muessise_adi', ''),
            '{xerite}'     => $clinic?->mapLink() ?? '',
        ];
    }

    /**
     * Turn a comma separated placeholder list ("{ad_soyad},{tarix}") into the
     * ordered parameter values a WhatsApp template body expects.
     *
     * @return list<string>
     */
    public function orderedParams(string $placeholderList, Appointment $appointment): array
    {
        return $this->orderedParamsFrom($placeholderList, $this->values($appointment));
    }

    /**
     * The same ordering, against an already resolved placeholder map — used by
     * greetings, which are built from a patient rather than an appointment.
     *
     * @param  array<string, string>  $values
     * @return list<string>
     */
    public function orderedParamsFrom(string $placeholderList, array $values): array
    {
        $params = [];

        foreach (explode(',', $placeholderList) as $placeholder) {
            $placeholder = trim($placeholder);

            if ($placeholder === '') {
                continue;
            }

            // Unknown placeholders are passed through as literal text so a typo
            // is visible in the log instead of silently shifting the order.
            $params[] = $values[$placeholder] ?? $placeholder;
        }

        return $params;
    }

    // -------------------------------------------------------------------------
    // Greetings
    // -------------------------------------------------------------------------

    /**
     * Fill in a birthday or holiday greeting.
     *
     * The caller has already picked the template it wants (the clinic's own
     * wording, or the admin's text for that particular holiday); an empty one
     * falls back to the global default for the occasion.
     *
     * @param  array<string, string>  $extra  Occasion specifics, e.g. ['{bayram}' => 'Novruz bayramı']
     */
    public function buildGreeting(?string $template, string $fallbackKey, Patient $patient, Clinic $clinic, array $extra = []): string
    {
        $template = trim((string) $template) !== ''
            ? (string) $template
            : (string) Setting::get($fallbackKey, self::GLOBAL_DEFAULTS[$fallbackKey] ?? '');

        $values = $this->greetingValues($patient, $clinic, $extra);

        return str_replace(array_keys($values), array_values($values), $template);
    }

    /**
     * Placeholder => value map for a greeting.
     *
     * @param  array<string, string>  $extra
     * @return array<string, string>
     */
    public function greetingValues(Patient $patient, Clinic $clinic, array $extra = []): array
    {
        return array_merge([
            '{ad}'       => $patient->name,
            '{ad_soyad}' => $patient->full_name,
            '{yas}'      => (string) ($patient->age ?? ''),
            '{bayram}'   => '',
            '{muessise}' => $clinic->name ?: (string) Setting::get('default_muessise_adi', ''),
            '{xerite}'   => $clinic->mapLink() ?? '',
        ], $extra);
    }
}
