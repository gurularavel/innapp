<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\SmsService;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send appointment reminder SMS messages (2 hours before)';

    public function __construct(private SmsService $smsService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $windowStart = now()->addMinutes(110); // 1h50m
        $windowEnd = now()->addMinutes(130);   // 2h10m

        $appointments = Appointment::with('patient', 'doctor')
            ->where('reminder_sent', false)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No appointments to remind.');
            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($appointments as $appointment) {
            $success = $this->smsService->sendReminderSms($appointment);

            if ($success) {
                $appointment->update(['reminder_sent' => true]);
                $sent++;
                $this->line("Reminder sent for appointment #{$appointment->id} to {$appointment->patient->full_name}");
            } else {
                $failed++;
                $this->warn("Failed to send reminder for appointment #{$appointment->id}");
            }
        }

        $this->info("Reminders sent: {$sent}, Failed: {$failed}");

        return self::SUCCESS;
    }
}
