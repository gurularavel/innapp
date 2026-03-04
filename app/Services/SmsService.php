<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\DoctorSubscription;
use App\Models\Setting;
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
        if ($doctorId) {
            $subscription = DoctorSubscription::where('doctor_id', $doctorId)
                ->where('is_active', true)
                ->where('expires_at', '>=', now()->toDateString())
                ->first();

            if ($subscription && $subscription->smsLimitReached()) {
                Log::warning("SMS limit reached for doctor #{$doctorId}");
                $this->logSms($phone, $message, $type, 'failed', $doctorId, $appointmentId, null);
                return false;
            }
        }

        ['success' => $success, 'receiver_id' => $receiverId, 'response_body' => $responseBody] = match ($this->driver) {
            'poctgoyercini' => $this->sendViaPostaGuvercini($phone, $message),
            default          => $this->sendViaLog($phone, $message),
        };

        $status = $success ? 'sent' : 'failed';
        $this->logSms($phone, $message, $type, $status, $doctorId, $appointmentId, $receiverId, $responseBody);

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
        $message = $this->buildMessage('sms_appointment_template', $appointment);

        return $this->send(
            $appointment->patient->phone,
            $message,
            $appointment->doctor_id,
            'appointment',
            $appointment->id
        );
    }

    public function sendReminderSms(Appointment $appointment): bool
    {
        $message = $this->buildMessage('sms_reminder_template', $appointment);

        return $this->send(
            $appointment->patient->phone,
            $message,
            $appointment->doctor_id,
            'reminder',
            $appointment->id
        );
    }

    private function buildMessage(string $templateKey, Appointment $appointment): string
    {
        $globalDefaults = [
            'sms_appointment_template' => 'Hörmətli {ad_soyad}, {tarix} {saat} randevunuz təsdiqləndi.',
            'sms_reminder_template'    => 'Xatırlatma: {ad_soyad}, {tarix} {saat} randevunuz var.',
        ];

        // User's own template takes priority; fall back to global admin default
        $user     = $appointment->doctor;
        $template = ($user?->{$templateKey} ?? null)
            ?: Setting::get($templateKey, $globalDefaults[$templateKey] ?? '');

        $patient     = $appointment->patient;
        $scheduledAt = $appointment->scheduled_at;
        $service     = $appointment->treatmentType?->name ?? '';
        $muessise    = $user?->muessise_adi ?: Setting::get('default_muessise_adi', '');

        return str_replace(
            ['{ad_soyad}', '{xidmet}', '{tarix}', '{saat}', '{muessise}'],
            [$patient->name, $service, $scheduledAt->format('d.m.Y'), $scheduledAt->format('H:i'), $muessise],
            $template
        );
    }

    // -------------------------------------------------------------------------
    // Drivers
    // -------------------------------------------------------------------------

    /**
     * Send via Posta Güvercini SMS API (v1.0.4).
     *
     * @return array{success: bool, receiver_id: string|null, response_body: array|null}
     */
    private function sendViaPostaGuvercini(string $phone, string $message): array
    {
        $apiUrl     = rtrim(config('services.sms.api_url'), '/');
        $publicKey  = config('services.sms.public_key');
        $privateKey = config('services.sms.private_key');
        $originator = config('services.sms.originator');

        $phone = $this->normalizePhone($phone);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $privateKey,
                'Content-Type'  => 'application/json',
            ])->post("{$apiUrl}/gateway/api/sms/v1/message/send?publicKey={$publicKey}", [
                'Text'    => $message,
                'Purpose' => 'INF',
                'Options' => [
                    'Originator' => $originator,
                    'Encoding'   => 'LATIN',
                ],
                'Receivers' => [
                    ['Receiver' => $phone],
                ],
            ]);

            $body = $response->json();

            if ((int) ($body['Status'] ?? 0) === 200) {
                $accepted   = $body['Result']['ReceiversAccepted'] ?? [];
                $receiverId = $accepted[0]['id'] ?? null;

                if (!empty($accepted)) {
                    return ['success' => true, 'receiver_id' => (string) $receiverId, 'response_body' => $body];
                }

                $rejected = $body['Result']['ReceiversRejected'][0] ?? [];
                Log::warning('Posta Güvercini receiver rejected', [
                    'phone'         => $phone,
                    'error_code'    => $rejected['ErrorCode'] ?? null,
                    'error_message' => $rejected['ErrorMessage'] ?? null,
                ]);
            } else {
                Log::error('Posta Güvercini API error', [
                    'status'      => $body['Status'] ?? null,
                    'description' => $body['Description'] ?? null,
                ]);
            }

            return ['success' => false, 'receiver_id' => null, 'response_body' => $body];
        } catch (\Exception $e) {
            Log::error('Posta Güvercini SMS exception: ' . $e->getMessage());
        }

        return ['success' => false, 'receiver_id' => null, 'response_body' => null];
    }

    /**
     * Log-only driver (development / testing).
     *
     * @return array{success: bool, receiver_id: null, response_body: array}
     */
    private function sendViaLog(string $phone, string $message): array
    {
        $body = [
            'driver'  => 'log',
            'phone'   => $phone,
            'message' => $message,
            'sent_at' => now()->toDateTimeString(),
        ];

        Log::channel('single')->info('[SMS LOG DRIVER]', $body);

        return ['success' => true, 'receiver_id' => null, 'response_body' => $body];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Normalize an Azerbaijani phone number to international MSISDN format.
     * Examples:
     *   055 123 45 67  →  994551234567
     *   +994551234567  →  994551234567
     *   994551234567   →  994551234567
     */
    private function normalizePhone(string $phone): string
    {
        // Strip everything except digits
        $phone = preg_replace('/\D/', '', $phone);

        // +994... → 994...  (already stripped + above)
        // 0... → 994...
        if (str_starts_with($phone, '0')) {
            $phone = '994' . substr($phone, 1);
        }

        // Bare 9-digit number (e.g. 551234567) → 994...
        if (strlen($phone) === 9) {
            $phone = '994' . $phone;
        }

        return $phone;
    }

    private function logSms(
        string  $phone,
        string  $message,
        string  $type,
        string  $status,
        ?int    $doctorId,
        ?int    $appointmentId,
        ?string $receiverId,
        ?array  $responseBody = null
    ): void {
        SmsLog::create([
            'appointment_id' => $appointmentId,
            'doctor_id'      => $doctorId,
            'phone'          => $phone,
            'message'        => $message,
            'type'           => $type,
            'status'         => $status,
            'sent_at'        => $status === 'sent' ? now() : null,
            'receiver_id'    => $receiverId,
            'response_body'  => $responseBody,
        ]);

        Log::channel('sms')->info('SMS ' . strtoupper($status), [
            'phone'         => $phone,
            'type'          => $type,
            'doctor_id'     => $doctorId,
            'appointment_id'=> $appointmentId,
            'receiver_id'   => $receiverId,
            'response_body' => $responseBody,
        ]);
    }
}
