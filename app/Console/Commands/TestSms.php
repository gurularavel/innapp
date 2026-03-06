<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Setting;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestSms extends Command
{
    protected $signature = 'sms:test
                            {--phone= : Test phone number (e.g. 0551234567)}
                            {--appointment= : Test with a real appointment ID}
                            {--type=reminder : SMS type: reminder or appointment}
                            {--dry-run : Show what would be sent without actually sending}';

    protected $description = 'Test SMS sending — driver, credentials, and message building';

    public function __construct(private SmsService $smsService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $driver = config('services.sms.driver', 'log');

        $this->info("=== SMS Test ===");
        $this->line("Driver       : <comment>{$driver}</comment>");
        $this->line("API URL      : " . (config('services.sms.api_url') ?: '<error>NOT SET</error>'));
        $this->line("Public Key   : " . (config('services.sms.public_key') ? '✓ set' : '<error>NOT SET</error>'));
        $this->line("Private Key  : " . (config('services.sms.private_key') ? '✓ set' : '<error>NOT SET</error>'));
        $this->line("Originator   : " . (config('services.sms.originator') ?: '<error>NOT SET</error>'));
        $this->newLine();

        $reminderMin = (int) Setting::get('reminder_minutes_before', 120);
        $windowStart = now()->addMinutes($reminderMin - 10);
        $windowEnd   = now()->addMinutes($reminderMin + 10);

        $this->line("Reminder window: <comment>{$reminderMin} min before</comment>");
        $this->line("Current time   : " . now()->toDateTimeString());
        $this->line("Window start   : " . $windowStart->toDateTimeString());
        $this->line("Window end     : " . $windowEnd->toDateTimeString());
        $this->newLine();

        // --- Mode 1: Test with a real appointment ---
        if ($apptId = $this->option('appointment')) {
            $appointment = Appointment::with('patient', 'doctor', 'treatmentType')->find($apptId);

            if (! $appointment) {
                $this->error("Appointment #{$apptId} not found.");
                return self::FAILURE;
            }

            $this->line("Appointment #{$appointment->id}");
            $this->line("  Patient     : {$appointment->patient->full_name}");
            $this->line("  Phone       : {$appointment->patient->phone}");
            $this->line("  Scheduled   : {$appointment->scheduled_at}");
            $this->line("  Status      : {$appointment->status}");
            $this->line("  Reminder sent: " . ($appointment->reminder_sent ? 'YES' : 'no'));
            $this->newLine();

            if ($this->option('dry-run')) {
                $this->warn('[DRY RUN] Would send ' . $this->option('type') . ' SMS — skipping actual send.');
                return self::SUCCESS;
            }

            $type    = $this->option('type');
            $success = $type === 'appointment'
                ? $this->smsService->sendAppointmentSms($appointment)
                : $this->smsService->sendReminderSms($appointment);

            if ($success) {
                $this->info("✓ SMS sent successfully.");
            } else {
                $this->error("✗ SMS failed. Check storage/logs/sms.log for details.");
            }

            return $success ? self::SUCCESS : self::FAILURE;
        }

        // --- Mode 2: Test with a custom phone number ---
        if ($phone = $this->option('phone')) {
            $message = "InnApp test SMS: " . now()->format('d.m.Y H:i:s');

            $this->line("Sending test SMS to: <comment>{$phone}</comment>");
            $this->line("Message: {$message}");
            $this->newLine();

            if ($this->option('dry-run')) {
                $this->warn('[DRY RUN] Would send to ' . $phone . ' — skipping actual send.');
                return self::SUCCESS;
            }

            // Use the service directly (no subscription check for test)
            $method = new \ReflectionMethod($this->smsService, $driver === 'postaGuvercini' ? 'sendViaPostaGuvercini' : 'sendViaLog');
            $method->setAccessible(true);
            $result = $method->invoke($this->smsService, $phone, $message);

            if ($result['success']) {
                $this->info("✓ SMS sent. Receiver ID: " . ($result['receiver_id'] ?? 'n/a'));
            } else {
                $this->error("✗ SMS failed.");
                $this->line("Response: " . json_encode($result['response_body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return $result['success'] ? self::SUCCESS : self::FAILURE;
        }

        // --- Mode 3: Show upcoming appointments in reminder window ---
        $this->line("=== Upcoming appointments in reminder window ===");

        $appointments = Appointment::with('patient', 'doctor')
            ->where('reminder_sent', false)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
            ->get();

        if ($appointments->isEmpty()) {
            $this->warn("No appointments found in the reminder window.");
            $this->newLine();

            // Show the next 5 upcoming appointments for reference
            $this->line("Next upcoming appointments (reminder_sent=false):");
            $upcoming = Appointment::with('patient')
                ->where('reminder_sent', false)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('scheduled_at', '>', now())
                ->orderBy('scheduled_at')
                ->limit(5)
                ->get();

            if ($upcoming->isEmpty()) {
                $this->warn("  No upcoming appointments.");
            } else {
                foreach ($upcoming as $a) {
                    $this->line(sprintf(
                        "  #%d | %s | %s | %s",
                        $a->id,
                        $a->scheduled_at->format('d.m.Y H:i'),
                        $a->patient->full_name,
                        $a->patient->phone
                    ));
                }
            }
        } else {
            $this->info("Found {$appointments->count()} appointment(s) that WOULD get a reminder now:");
            foreach ($appointments as $a) {
                $this->line(sprintf(
                    "  #%d | %s | %s | %s",
                    $a->id,
                    $a->scheduled_at->format('d.m.Y H:i'),
                    $a->patient->full_name,
                    $a->patient->phone
                ));
            }
        }

        $this->newLine();
        $this->line("<comment>Usage examples:</comment>");
        $this->line("  php artisan sms:test                          # show status + upcoming appointments");
        $this->line("  php artisan sms:test --phone=0551234567       # send test SMS to a number");
        $this->line("  php artisan sms:test --appointment=5          # send reminder for appointment #5");
        $this->line("  php artisan sms:test --appointment=5 --dry-run  # preview without sending");

        return self::SUCCESS;
    }
}
