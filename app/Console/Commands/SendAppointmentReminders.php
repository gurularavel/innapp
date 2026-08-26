<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send appointment reminders over each user\'s chosen channel (SMS and/or WhatsApp)';

    public function __construct(private NotificationService $notifications)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $startedAt = now();
        $center      = (int) Setting::get('reminder_minutes_before', 120);
        $windowStart = now()->addMinutes($center - 10);
        $windowEnd   = now()->addMinutes($center + 10);

        Log::channel('cron')->info('reminders:send started', [
            'time'           => $startedAt->toDateTimeString(),
            'reminder_min'   => $center,
            'window_start'   => $windowStart->toDateTimeString(),
            'window_end'     => $windowEnd->toDateTimeString(),
        ]);

        $appointments = Appointment::with('patient', 'doctor')
            ->where('reminder_sent', false)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No appointments to remind.');
            Log::channel('cron')->info('reminders:send — no appointments found, finished.');
            return self::SUCCESS;
        }

        Log::channel('cron')->info("reminders:send — found {$appointments->count()} appointment(s) to process.");

        $sent = 0;
        $failed = 0;

        foreach ($appointments as $appointment) {
            $results = $this->notifications->results($appointment, 'reminder');
            $success = in_array(true, $results, true);
            $channels = implode(', ', array_keys($results));

            if ($success) {
                $appointment->update(['reminder_sent' => true]);
                $sent++;
                $this->line("Reminder sent for appointment #{$appointment->id} to {$appointment->patient->full_name} via {$channels}");
                Log::channel('cron')->info("Reminder sent: appointment #{$appointment->id}", [
                    'patient'      => $appointment->patient->full_name,
                    'scheduled_at' => $appointment->scheduled_at,
                    'channels'     => $results,
                ]);
            } else {
                $failed++;
                $this->warn("Failed to send reminder for appointment #{$appointment->id}");
                Log::channel('cron')->warning("Reminder FAILED: appointment #{$appointment->id}", [
                    'patient'      => $appointment->patient->full_name,
                    'scheduled_at' => $appointment->scheduled_at,
                    'channels'     => $results,
                ]);
            }
        }

        $this->info("Reminders sent: {$sent}, Failed: {$failed}");
        Log::channel('cron')->info("reminders:send finished. Sent: {$sent}, Failed: {$failed}, Duration: " . now()->diffInSeconds($startedAt) . "s");

        return self::SUCCESS;
    }
}
