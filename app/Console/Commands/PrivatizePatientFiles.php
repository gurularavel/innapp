<?php

namespace App\Console\Commands;

use App\Models\Patient;
use App\Models\PatientFieldValue;
use App\Models\PatientVisitFile;
use App\Support\PatientFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Moves patient uploads written before the private-disk change out of
 * public/storage. Safe to run more than once — files already moved are skipped.
 */
class PrivatizePatientFiles extends Command
{
    protected $signature = 'patient-files:privatize {--dry-run : Only report what would be moved}';
    protected $description = 'Move patient photos, visit files and custom-field files from the public disk to the private one';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $moved  = 0;
        $missing = 0;

        $paths = collect()
            ->merge(Patient::whereNotNull('photo')->pluck('photo'))
            ->merge(PatientVisitFile::pluck('file_path'))
            ->merge(
                PatientFieldValue::whereNotNull('value')
                    ->whereHas('specialtyField', fn ($q) => $q->where('type', 'file'))
                    ->pluck('value')
            )
            ->filter()
            ->unique();

        Log::channel('cron')->info('patient-files:privatize started', ['candidates' => $paths->count(), 'dry_run' => $dryRun]);

        foreach ($paths as $path) {
            $disk = PatientFiles::diskOf($path);

            if ($disk === null) {
                $missing++;
                $this->warn("missing: {$path}");
                continue;
            }

            if ($disk === PatientFiles::DISK) {
                continue;
            }

            if ($dryRun) {
                $this->line("would move: {$path}");
                $moved++;
                continue;
            }

            if (PatientFiles::privatize($path)) {
                $moved++;
                $this->line("moved: {$path}");
            }
        }

        $this->info(($dryRun ? 'Would move' : 'Moved') . ": {$moved}, missing: {$missing}");
        Log::channel('cron')->info('patient-files:privatize finished', ['moved' => $moved, 'missing' => $missing, 'dry_run' => $dryRun]);

        return self::SUCCESS;
    }
}
