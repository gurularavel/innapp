<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\SmsLog;
use App\Services\Concerns\NormalizesPhone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    use NormalizesPhone;

    private string $driver;

    public function __construct(private MessageBuilder $builder)
    {
        $this->driver = config('services.sms.driver', 'log');
    }

    /**
     * Messaging is unlimited under seat based pricing, so there is no quota to
     * check or decrement here — only delivery and logging.
     *
     * @param  array{patient_id?: int|null, reference?: string|null}  $context
     *         Extra bookkeeping for greetings: who it was for and which
     *         occasion, so the same greeting is never sent twice.
     */
    public function send(
        string $phone,
        string $message,
        ?int   $doctorId = null,
        string $type = 'custom',
        ?int   $appointmentId = null,
        ?int   $clinicId = null,
        array  $context = []
    ): bool {
        // Demo clinics never reach the gateway — their data looks real but is not.
        $driver = $this->isDemoClinic($clinicId) ? 'log' : $this->driver;

        ['success' => $success, 'receiver_id' => $receiverId, 'response_body' => $responseBody] = match ($driver) {
            'poctgoyercini' => $this->sendViaPostaGuvercini($phone, $message),
            default          => $this->sendViaLog($phone, $message),
        };

        $status = $success ? 'sent' : 'failed';
        $this->logSms($phone, $message, $type, $status, $doctorId, $appointmentId, $receiverId, $responseBody, $clinicId, $context);

        return $success;
    }

    /**
     * Birthday / holiday greeting for one patient.
     *
     * `$reference` identifies the occasion ("birthday:2026-08-26") and is
     * written to the log so a repeated run recognises it as already handled.
     */
    public function sendGreeting(
        Patient $patient,
        Clinic  $clinic,
        string  $type,
        string  $message,
        string  $reference
    ): bool {
        return $this->send(
            $patient->phone,
            $message,
            null,
            $type,
            null,
            $clinic->id,
            ['patient_id' => $patient->id, 'reference' => $reference]
        );
    }

    public function sendAppointmentSms(Appointment $appointment): bool
    {
        $message = $this->builder->build('sms_appointment_template', $appointment);

        return $this->send(
            $appointment->patient->phone,
            $message,
            $appointment->doctor_id,
            'appointment',
            $appointment->id,
            $appointment->clinic_id
        );
    }

    public function sendReminderSms(Appointment $appointment): bool
    {
        $message = $this->builder->build('sms_reminder_template', $appointment);

        return $this->send(
            $appointment->patient->phone,
            $message,
            $appointment->doctor_id,
            'reminder',
            $appointment->id,
            $appointment->clinic_id
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

    private function isDemoClinic(?int $clinicId): bool
    {
        return $clinicId !== null && (bool) Clinic::find($clinicId)?->isDemo();
    }

    private function logSms(
        string  $phone,
        string  $message,
        string  $type,
        string  $status,
        ?int    $doctorId,
        ?int    $appointmentId,
        ?string $receiverId,
        ?array  $responseBody = null,
        ?int    $clinicId = null,
        array   $context = []
    ): void {
        SmsLog::create([
            'appointment_id' => $appointmentId,
            'patient_id'     => $context['patient_id'] ?? null,
            'clinic_id'      => $clinicId,
            'doctor_id'      => $doctorId,
            'phone'          => $phone,
            'message'        => $message,
            'type'           => $type,
            'reference'      => $context['reference'] ?? null,
            'channel'        => 'sms',
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
