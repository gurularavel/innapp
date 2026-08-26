<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Setting;
use App\Services\MessageBuilder;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class TestWhatsapp extends Command
{
    protected $signature = 'whatsapp:test
                            {--phone= : Test phone number (e.g. 0551234567)}
                            {--appointment= : Test with a real appointment ID}
                            {--type=reminder : Message type: reminder or appointment}
                            {--dry-run : Show what would be sent without actually sending}';

    protected $description = 'Test WhatsApp sending — configuration, templates, and message building';

    public function __construct(
        private WhatsAppService $whatsapp,
        private NotificationService $notifications,
        private MessageBuilder $builder,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $type = $this->option('type') === 'appointment' ? 'appointment' : 'reminder';

        $this->info('=== WhatsApp Test ===');
        $this->line('Enabled         : ' . (Setting::get('whatsapp_enabled', '0') === '1' ? '<info>yes</info>' : '<comment>no</comment>'));
        $this->line('Configured      : ' . ($this->whatsapp->isConfigured() ? '<info>yes</info>' : '<comment>no — log driver will be used</comment>'));
        $this->line('API version     : ' . Setting::get('whatsapp_api_version', 'v21.0'));
        $this->line('Phone Number ID : ' . (Setting::get('whatsapp_phone_number_id') ?: '<comment>NOT SET</comment>'));
        $this->line('Access Token    : ' . (Setting::get('whatsapp_access_token') ? '✓ set' : '<comment>NOT SET</comment>'));
        $this->line('Language        : ' . Setting::get('whatsapp_language_code', 'az'));
        $this->line("Template ({$type}) : " . (Setting::get("whatsapp_{$type}_template") ?: '<comment>none — free-form text mode</comment>'));
        $this->line("Params ({$type})   : " . (Setting::get("whatsapp_{$type}_params") ?: '—'));
        $this->newLine();

        // --- Mode 1: real appointment ---
        if ($apptId = $this->option('appointment')) {
            $appointment = Appointment::with('patient', 'doctor', 'treatmentType')->find($apptId);

            if (! $appointment) {
                $this->error("Appointment #{$apptId} not found.");
                return self::FAILURE;
            }

            $message  = $this->builder->build("sms_{$type}_template", $appointment);
            $channels = $this->notifications->channelsFor($appointment->doctor);

            $this->line("Appointment #{$appointment->id}");
            $this->line("  Patient   : {$appointment->patient->full_name}");
            $this->line("  Phone     : {$appointment->patient->phone}");
            $this->line("  Scheduled : {$appointment->scheduled_at}");
            $this->line('  User pref : ' . ($appointment->doctor?->notify_channel ?? 'sms'));
            $this->line('  Effective : ' . implode(', ', $channels));
            $this->line("  Message   : {$message}");

            if ($params = Setting::get("whatsapp_{$type}_params")) {
                $this->line('  Params    : ' . json_encode(
                    $this->builder->orderedParams($params, $appointment),
                    JSON_UNESCAPED_UNICODE
                ));
            }
            $this->newLine();

            if ($this->option('dry-run')) {
                $this->warn("[DRY RUN] Would send the {$type} WhatsApp message — skipping actual send.");
                return self::SUCCESS;
            }

            $success = $type === 'appointment'
                ? $this->whatsapp->sendAppointmentMessage($appointment)
                : $this->whatsapp->sendReminderMessage($appointment);

            if ($success) {
                $this->info('✓ WhatsApp message sent.');
            } else {
                $this->error('✗ WhatsApp send failed. Check storage/logs/sms.log for details.');
            }

            return $success ? self::SUCCESS : self::FAILURE;
        }

        // --- Mode 2: custom phone number ---
        if ($phone = $this->option('phone')) {
            $message = 'InnApp test WhatsApp: ' . now()->format('d.m.Y H:i:s');

            $this->line("Sending test message to: <comment>{$phone}</comment>");
            $this->line("Message: {$message}");
            $this->newLine();

            if ($this->option('dry-run')) {
                $this->warn("[DRY RUN] Would send to {$phone} — skipping actual send.");
                return self::SUCCESS;
            }

            // No doctor id — this test must not consume anyone's package limit.
            $success = $this->whatsapp->send($phone, $message, null, 'custom');

            if ($success) {
                $this->info('✓ WhatsApp message accepted.');
            } else {
                $this->error('✗ WhatsApp send failed. Check storage/logs/sms.log for details.');
            }

            return $success ? self::SUCCESS : self::FAILURE;
        }

        $this->line('<comment>Usage examples:</comment>');
        $this->line('  php artisan whatsapp:test                                # show configuration');
        $this->line('  php artisan whatsapp:test --phone=0551234567             # send a test message');
        $this->line('  php artisan whatsapp:test --appointment=5                # send reminder for appointment #5');
        $this->line('  php artisan whatsapp:test --appointment=5 --dry-run      # preview without sending');

        return self::SUCCESS;
    }
}
