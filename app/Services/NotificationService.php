<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;

/**
 * Decides which channel(s) an appointment message goes out on and dispatches it.
 *
 * The channel is a clinic-wide setting, so every member sends over the same
 * channel. When the admin has not enabled WhatsApp yet, WhatsApp is dropped from
 * the selection and SMS is used instead — a preference must never silence
 * notifications.
 */
class NotificationService
{
    public function __construct(
        private SmsService $sms,
        private WhatsAppService $whatsapp,
    ) {}

    /**
     * Channels that will actually be used for this clinic.
     *
     * Accepts either a clinic or any of its members, since callers have one or
     * the other at hand.
     *
     * @return list<string>
     */
    public function channelsFor(Clinic|User|null $subject): array
    {
        $clinic = $subject instanceof User ? $subject->clinic : $subject;

        $preferred = $clinic?->notify_channel ?: 'sms';

        $channels = match ($preferred) {
            'whatsapp' => ['whatsapp'],
            'both'     => ['sms', 'whatsapp'],
            default    => ['sms'],
        };

        if (!$this->whatsapp->isConfigured()) {
            $channels = array_values(array_diff($channels, ['whatsapp']));
        }

        return $channels ?: ['sms'];
    }

    /**
     * Send the appointment confirmation. True when at least one channel accepted it.
     */
    public function sendAppointment(Appointment $appointment): bool
    {
        return $this->dispatch($appointment, 'appointment');
    }

    /**
     * Send the appointment reminder. True when at least one channel accepted it.
     */
    public function sendReminder(Appointment $appointment): bool
    {
        return $this->dispatch($appointment, 'reminder');
    }

    /**
     * Per-channel result, useful for the test command and for reporting.
     *
     * @return array<string, bool>
     */
    public function results(Appointment $appointment, string $type): array
    {
        $results = [];

        $subject = $appointment->clinic ?? $appointment->doctor;

        foreach ($this->channelsFor($subject) as $channel) {
            $results[$channel] = $this->sendVia($channel, $appointment, $type);
        }

        return $results;
    }

    /**
     * Send a birthday or holiday greeting over the clinic's channel(s).
     *
     * The text is rendered by the caller, which knows which template won; here
     * only the channel routing and the per-channel bookkeeping are decided.
     *
     * @param  string  $type  'birthday' or 'holiday'
     * @param  string  $reference  Occasion key that keeps a re-run from greeting twice
     * @param  list<string>  $templateParams  Ordered values for the WhatsApp template body
     * @return array<string, bool>
     */
    public function sendGreeting(
        Patient $patient,
        Clinic  $clinic,
        string  $type,
        string  $message,
        string  $reference,
        array   $templateParams = []
    ): array {
        $results = [];

        foreach ($this->channelsFor($clinic) as $channel) {
            $results[$channel] = $channel === 'whatsapp'
                ? $this->whatsapp->sendGreeting($patient, $clinic, $type, $message, $reference, $templateParams)
                : $this->sms->sendGreeting($patient, $clinic, $type, $message, $reference);
        }

        return $results;
    }

    private function dispatch(Appointment $appointment, string $type): bool
    {
        return in_array(true, $this->results($appointment, $type), true);
    }

    private function sendVia(string $channel, Appointment $appointment, string $type): bool
    {
        if ($channel === 'whatsapp') {
            return $type === 'appointment'
                ? $this->whatsapp->sendAppointmentMessage($appointment)
                : $this->whatsapp->sendReminderMessage($appointment);
        }

        return $type === 'appointment'
            ? $this->sms->sendAppointmentSms($appointment)
            : $this->sms->sendReminderSms($appointment);
    }
}
