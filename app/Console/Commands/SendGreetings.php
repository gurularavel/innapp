<?php

namespace App\Console\Commands;

use App\Services\GreetingService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SendGreetings extends Command
{
    protected $signature = 'greetings:send
                            {--force : Run regardless of the configured send hour}
                            {--dry-run : Show what would go out without sending anything}
                            {--clinic= : Limit the run to a single clinic id}';

    protected $description = 'Send birthday and holiday greetings over each clinic\'s chosen channel (SMS and/or WhatsApp)';

    public function __construct(private GreetingService $greetings)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $now      = now();
        $sendHour = $this->greetings->sendHour();
        $dryRun   = (bool) $this->option('dry-run');

        // The scheduler ticks hourly; the admin's hour decides which tick acts.
        if (! $this->option('force') && $now->hour !== $sendHour) {
            return self::SUCCESS;
        }

        $startedAt = $now->copy();

        Log::channel('cron')->info('greetings:send started', [
            'time'      => $startedAt->toDateTimeString(),
            'send_hour' => $sendHour,
            'dry_run'   => $dryRun,
        ]);

        $query = $this->greetings->eligibleClinics();

        if ($clinicId = $this->option('clinic')) {
            $query->whereKey($clinicId);
        }

        $totals   = ['sent' => 0, 'failed' => 0];
        $clinics  = 0;
        $previews = [];

        $query->chunkById(50, function (Collection $batch) use ($now, $dryRun, &$totals, &$clinics, &$previews) {
            foreach ($batch as $clinic) {
                $clinics++;

                $birthdays = $this->greetings->sendBirthdayGreetings($clinic, $now, $dryRun);
                $holidays  = $this->greetings->sendHolidayGreetings($clinic, $now, $dryRun);

                $sent   = $birthdays['sent'] + $holidays['sent'];
                $failed = $birthdays['failed'] + $holidays['failed'];

                $totals['sent']   += $sent;
                $totals['failed'] += $failed;
                $previews = array_merge($previews, $birthdays['preview'], $holidays['preview']);

                if ($sent === 0 && $failed === 0) {
                    continue;
                }

                $this->line(sprintf(
                    '%s (#%d): ad günü %d, bayram %d, uğursuz %d',
                    $clinic->name,
                    $clinic->id,
                    $birthdays['sent'],
                    $holidays['sent'],
                    $failed
                ));

                Log::channel('cron')->info("greetings:send — klinika #{$clinic->id}", [
                    'clinic'    => $clinic->name,
                    'birthdays' => $birthdays['sent'],
                    'holidays'  => $holidays['sent'],
                    'failed'    => $failed,
                    'dry_run'   => $dryRun,
                ]);
            }
        });

        $this->reportPreviews($previews, $dryRun);

        $summary = sprintf(
            '%s%d klinika yoxlanıldı. Göndərildi: %d, uğursuz: %d.',
            $dryRun ? '[SINAQ] ' : '',
            $clinics,
            $totals['sent'],
            $totals['failed']
        );

        $this->info($summary);

        Log::channel('cron')->info('greetings:send finished. ' . $summary . ' Duration: '
            . now()->diffInSeconds($startedAt) . 's');

        return self::SUCCESS;
    }

    /** @param  list<array{phone: string, message: string}>  $previews */
    private function reportPreviews(array $previews, bool $dryRun): void
    {
        if (! $dryRun || $previews === []) {
            return;
        }

        $this->newLine();
        $this->comment('Nümunə mesajlar (ilk ' . count($previews) . '):');

        foreach ($previews as $preview) {
            $this->line('  ' . $preview['phone'] . ' → ' . $preview['message']);
        }

        $this->newLine();
    }
}
