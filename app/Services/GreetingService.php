<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\ClinicHolidaySetting;
use App\Models\Holiday;
use App\Models\Setting;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Decides who gets a birthday or holiday greeting today, and with what wording.
 *
 * Greetings are opt-in per clinic and go out at one hour of the day set by the
 * admin. Every attempt is written to `sms_logs` with an occasion `reference`,
 * which is also what stops a second run of the scheduler from greeting the same
 * patient twice — an attempt counts whether it succeeded or not, so a bad phone
 * number cannot turn into a retry storm.
 */
class GreetingService
{
    /** How many patients are loaded per query while fanning out. */
    private const CHUNK = 200;

    public function __construct(
        private MessageBuilder $builder,
        private NotificationService $notifications,
    ) {}

    /** The hour of day greetings go out, platform-wide. */
    public function sendHour(): int
    {
        return max(0, min(23, (int) Setting::get('greetings_send_hour', 9)));
    }

    /**
     * Clinics allowed to greet at all: switched on, active, paid up, not a demo.
     *
     * Demo clinics are seeded with realistic looking phone numbers that belong
     * to nobody, so they must never reach a gateway.
     */
    public function eligibleClinics(): Builder
    {
        return Clinic::query()
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q
                ->where('birthday_greetings_enabled', true)
                ->orWhere('holiday_greetings_enabled', true))
            ->whereHas('subscriptions', fn (Builder $q) => $q
                ->where('is_active', true)
                ->whereDate('expires_at', '>=', now()->toDateString()))
            ->whereDoesntHave('members', fn (Builder $q) => $q->where('is_demo', true));
    }

    // -------------------------------------------------------------------------
    // Birthdays
    // -------------------------------------------------------------------------

    /**
     * @return array{sent: int, failed: int, preview: list<array{phone: string, message: string}>}
     */
    public function sendBirthdayGreetings(Clinic $clinic, CarbonInterface $date, bool $dryRun = false): array
    {
        $stats = $this->emptyStats();

        if (! $clinic->birthday_greetings_enabled) {
            return $stats;
        }

        $reference  = 'birthday:' . $date->format('Y-m-d');
        $recipients = $this->birthdayFilter($this->pendingRecipients($clinic, $reference), $date);

        $this->greet($clinic, $recipients, 'birthday', $clinic->sms_birthday_template, $reference, [], $dryRun, $stats);

        return $stats;
    }

    /**
     * Patients whose birthday falls on this date.
     *
     * A 29 February birthday is greeted on the 28th in a common year — otherwise
     * it would be skipped three years out of four.
     */
    private function birthdayFilter(HasMany $query, CarbonInterface $date): HasMany
    {
        return $query->where(function (Builder $q) use ($date) {
            $q->where(fn (Builder $inner) => $inner
                ->whereMonth('birth_date', $date->month)
                ->whereDay('birth_date', $date->day));

            if ($date->month === 2 && $date->day === 28 && ! $date->isLeapYear()) {
                $q->orWhere(fn (Builder $inner) => $inner
                    ->whereMonth('birth_date', 2)
                    ->whereDay('birth_date', 29));
            }
        });
    }

    // -------------------------------------------------------------------------
    // Holidays
    // -------------------------------------------------------------------------

    /**
     * @return array{sent: int, failed: int, preview: list<array{phone: string, message: string}>}
     */
    public function sendHolidayGreetings(Clinic $clinic, CarbonInterface $date, bool $dryRun = false): array
    {
        $stats = $this->emptyStats();

        if (! $clinic->holiday_greetings_enabled) {
            return $stats;
        }

        foreach ($this->holidaysFor($clinic, $date) as ['holiday' => $holiday, 'template' => $template]) {
            $reference = 'holiday:' . $holiday->id . ':' . $date->year;

            $this->greet(
                $clinic,
                $this->pendingRecipients($clinic, $reference),
                'holiday',
                $template,
                $reference,
                ['{bayram}' => $holiday->name],
                $dryRun,
                $stats
            );
        }

        return $stats;
    }

    /**
     * The holidays this clinic greets on for the given date, each paired with
     * the wording that wins for it (null = fall back to the global default).
     *
     * @return Collection<int, array{holiday: Holiday, template: string|null}>
     */
    public function holidaysFor(Clinic $clinic, CarbonInterface $date): Collection
    {
        $holidays = Holiday::query()
            ->active()
            ->onDate($date)
            ->where(fn (Builder $q) => $q->whereNull('clinic_id')->orWhere('clinic_id', $clinic->id))
            ->get();

        if ($holidays->isEmpty()) {
            return collect();
        }

        $overrides = $clinic->holidaySettings()
            ->whereIn('holiday_id', $holidays->pluck('id'))
            ->get()
            ->keyBy('holiday_id');

        return $holidays
            // A clinic's own date answers to its own flag; a shared one can be
            // switched off per clinic, and counts as on until it is.
            ->filter(fn (Holiday $holiday) => ! $holiday->isShared()
                || ($overrides->get($holiday->id)?->is_enabled ?? true))
            ->map(fn (Holiday $holiday) => [
                'holiday'  => $holiday,
                'template' => $this->holidayTemplate($holiday, $overrides->get($holiday->id)),
            ])
            ->values();
    }

    /** Clinic wording » the admin's text for this holiday » global default. */
    private function holidayTemplate(Holiday $holiday, ?ClinicHolidaySetting $override): ?string
    {
        foreach ([$override?->template, $holiday->template] as $candidate) {
            if (trim((string) $candidate) !== '') {
                return (string) $candidate;
            }
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * Patients of this clinic who can be reached and have not been greeted for
     * this occasion yet.
     */
    private function pendingRecipients(Clinic $clinic, string $reference): HasMany
    {
        return $clinic->patients()
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereNotExists(fn ($query) => $query
                ->select(DB::raw(1))
                ->from('sms_logs')
                ->whereColumn('sms_logs.patient_id', 'patients.id')
                ->where('sms_logs.reference', $reference));
    }

    /**
     * Render and dispatch one occasion to every pending recipient.
     *
     * @param  array<string, string>  $extra
     * @param  array{sent: int, failed: int, preview: list<array{phone: string, message: string}>}  $stats
     */
    private function greet(
        Clinic  $clinic,
        HasMany $recipients,
        string  $type,
        ?string $template,
        string  $reference,
        array   $extra,
        bool    $dryRun,
        array   &$stats
    ): void {
        $fallbackKey = "sms_{$type}_template";
        $paramList   = (string) Setting::get("whatsapp_{$type}_params", '');

        $recipients->chunkById(self::CHUNK, function (Collection $patients) use (
            $clinic, $type, $template, $reference, $extra, $dryRun, $fallbackKey, $paramList, &$stats
        ) {
            foreach ($patients as $patient) {
                $message = $this->builder->buildGreeting($template, $fallbackKey, $patient, $clinic, $extra);

                // An empty result means nothing was configured anywhere — send
                // nothing rather than a blank message.
                if (trim($message) === '') {
                    continue;
                }

                if ($dryRun) {
                    $stats['sent']++;

                    if (count($stats['preview']) < 5) {
                        $stats['preview'][] = ['phone' => $patient->phone, 'message' => $message];
                    }

                    continue;
                }

                $params = $this->builder->orderedParamsFrom(
                    $paramList,
                    $this->builder->greetingValues($patient, $clinic, $extra)
                );

                $results = $this->notifications->sendGreeting($patient, $clinic, $type, $message, $reference, $params);

                in_array(true, $results, true) ? $stats['sent']++ : $stats['failed']++;
            }
        });
    }

    /**
     * @return array{sent: int, failed: int, preview: list<array{phone: string, message: string}>}
     */
    private function emptyStats(): array
    {
        return ['sent' => 0, 'failed' => 0, 'preview' => []];
    }
}
