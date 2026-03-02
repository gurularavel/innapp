<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\DoctorSubscription;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $driver;

    public function __construct()
    {
        $this->driver = config('services.sms.driver', 'log');
    }

    public function send(string $phone, string $message, ?int $doctorId = null, string $type = 'custom', ?int $appointmentId = null): bool
    {
        // Check subscription SMS limit
        if ($doctorId) {
            $subscription = DoctorSubscription::where('doctor_id', $doctorId)
                ->where('is_active', true)
                ->where('expires_at', '>=', now()->toDateString())
                ->first();

            if ($subscription && $subscription->smsLimitReached()) {
                Log::warning("SMS limit reached for doctor #{$doctorId}");
                $this->logSms($phone, $message, $type, 'failed', $doctorId, $appointmentId);
                return false;
            }
        }

        $success = match($this->driver) {
            'http' => $this->sendViaHttp($phone, $message),
            default => $this->sendViaLog($phone, $message),
        };

        $status = $success ? 'sent' : 'failed';
        $this->logSms($phone, $message, $type, $status, $doctorId, $appointmentId);

        if ($success && $doctorId) {
            DoctorSubscription::where('doctor_id', $doctorId)
                ->where('is_active', true)
                ->where('expires_at', '>=', now()->toDateString())
                ->increment('sms_used');
        }

        return $success;
    }

    public function sendAppointmentSms(Appointment $appointment): bool
    {
        $patient = $appointment->patient;
        $scheduledAt = $appointment->scheduled_at;

        $message = "Hörmətli {$patient->name}, "
            . $scheduledAt->format('d.m.Y') . ' '
            . $scheduledAt->format('H:i')
            . ' randevunuz təsdiqləndi.';

        return $this->send(
            $patient->phone,
            $message,
            $appointment->doctor_id,
            'appointment',
            $appointment->id
        );
    }

    public function sendReminderSms(Appointment $appointment): bool
    {
        $patient = $appointment->patient;
        $scheduledAt = $appointment->scheduled_at;

        $message = "Xatırlatma: {$patient->name}, "
            . $scheduledAt->format('d.m.Y') . ' '
            . $scheduledAt->format('H:i')
            . ' randevunuz var.';

        return $this->send(
            $patient->phone,
            $message,
            $appointment->doctor_id,
            'reminder',
            $appointment->id
        );
    }

    private function sendViaLog(string $phone, string $message): bool
    {
        Log::channel('single')->info('[SMS LOG DRIVER]', [
            'phone' => $phone,
            'message' => $message,
            'sent_at' => now()->toDateTimeString(),
        ]);

        return true;
    }

    private function sendViaHttp(string $phone, string $message): bool
    {
        try {
            $response = Http::post(config('services.sms.url'), [
                'username' => config('services.sms.username'),
                'password' => config('services.sms.password'),
                'phone' => $phone,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('SMS HTTP send failed: ' . $e->getMessage());
            return false;
        }
    }

    private function logSms(
        string $phone,
        string $message,
        string $type,
        string $status,
        ?int $doctorId,
        ?int $appointmentId
    ): void {
        SmsLog::create([
            'appointment_id' => $appointmentId,
            'doctor_id' => $doctorId,
            'phone' => $phone,
            'message' => $message,
            'type' => $type,
            'status' => $status,
            'sent_at' => $status === 'sent' ? now() : null,
        ]);
    }
}
