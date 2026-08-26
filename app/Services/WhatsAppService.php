<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Services\Concerns\NormalizesPhone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends WhatsApp messages through the Meta WhatsApp Cloud API.
 *
 * Credentials and template names are configured by the admin from
 * Ayarlar » WhatsApp; the .env values act as a fallback.
 */
class WhatsAppService
{
    use NormalizesPhone;

    public function __construct(private MessageBuilder $builder) {}

    /**
     * WhatsApp is usable only when the admin enabled it and the credentials are filled in.
     */
    public function isConfigured(): bool
    {
        return Setting::get('whatsapp_enabled', '0') === '1'
            && $this->phoneNumberId() !== ''
            && $this->accessToken() !== '';
    }

    /**
     * Messaging is unlimited under seat based pricing, so there is no quota to
     * check or decrement here — only delivery and logging.
     */
    public function send(
        string  $phone,
        string  $message,
        ?int    $doctorId = null,
        string  $type = 'custom',
        ?int    $appointmentId = null,
        ?string $templateName = null,
        array   $templateParams = [],
        ?int    $clinicId = null,
        array   $context = []
    ): bool {
        ['success' => $success, 'message_id' => $messageId, 'response_body' => $responseBody] =
            $this->isConfigured()
                ? $this->sendViaCloudApi($phone, $message, $templateName, $templateParams)
                : $this->sendViaLog($phone, $message, $templateName, $templateParams);

        $status = $success ? 'sent' : 'failed';
        $this->logMessage($phone, $message, $type, $status, $doctorId, $appointmentId, $messageId, $responseBody, $clinicId, $context);

        return $success;
    }

    /**
     * Birthday / holiday greeting for one patient.
     *
     * A greeting almost always falls outside Meta's 24 hour service window, so
     * an approved template is what actually gets delivered; the plain text is
     * still passed along for the log and for the free-form fallback.
     *
     * @param  list<string>  $templateParams  Ordered values for the template body
     */
    public function sendGreeting(
        Patient $patient,
        Clinic  $clinic,
        string  $type,
        string  $message,
        string  $reference,
        array   $templateParams = []
    ): bool {
        $templateName = trim((string) Setting::get("whatsapp_{$type}_template", ''));

        return $this->send(
            $patient->phone,
            $message,
            null,
            $type,
            null,
            $templateName ?: null,
            $templateName !== '' ? $templateParams : [],
            $clinic->id,
            ['patient_id' => $patient->id, 'reference' => $reference]
        );
    }

    public function sendAppointmentMessage(Appointment $appointment): bool
    {
        return $this->sendForAppointment($appointment, 'appointment');
    }

    public function sendReminderMessage(Appointment $appointment): bool
    {
        return $this->sendForAppointment($appointment, 'reminder');
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function sendForAppointment(Appointment $appointment, string $type): bool
    {
        $message      = $this->builder->build("sms_{$type}_template", $appointment);
        $templateName = trim((string) Setting::get("whatsapp_{$type}_template", ''));

        $params = $templateName !== ''
            ? $this->builder->orderedParams((string) Setting::get("whatsapp_{$type}_params", ''), $appointment)
            : [];

        return $this->send(
            $appointment->patient->phone,
            $message,
            $appointment->doctor_id,
            $type,
            $appointment->id,
            $templateName ?: null,
            $params,
            $appointment->clinic_id
        );
    }

    /**
     * @return array{success: bool, message_id: string|null, response_body: array|null}
     */
    private function sendViaCloudApi(string $phone, string $message, ?string $templateName, array $templateParams): array
    {
        $phone   = $this->normalizePhone($phone);
        $version = Setting::get('whatsapp_api_version', config('services.whatsapp.api_version', 'v21.0'));
        $url     = "https://graph.facebook.com/{$version}/{$this->phoneNumberId()}/messages";

        $payload = $templateName
            ? $this->templatePayload($phone, $templateName, $templateParams)
            : $this->textPayload($phone, $message);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken(),
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post($url, $payload);

            $body = $response->json();

            if ($response->successful() && !empty($body['messages'][0]['id'])) {
                return [
                    'success'       => true,
                    'message_id'    => (string) $body['messages'][0]['id'],
                    'response_body' => $body,
                ];
            }

            Log::error('WhatsApp Cloud API error', [
                'phone'   => $phone,
                'status'  => $response->status(),
                'error'   => $body['error']['message'] ?? null,
                'code'    => $body['error']['code'] ?? null,
            ]);

            return ['success' => false, 'message_id' => null, 'response_body' => $body];
        } catch (\Exception $e) {
            Log::error('WhatsApp Cloud API exception: ' . $e->getMessage());
        }

        return ['success' => false, 'message_id' => null, 'response_body' => null];
    }

    /**
     * Free-form text message. Meta only delivers these inside the 24 hour
     * customer service window — outside of it an approved template is required.
     */
    private function textPayload(string $phone, string $message): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $phone,
            'type'              => 'text',
            'text'              => [
                'preview_url' => true,
                'body'        => $message,
            ],
        ];
    }

    /**
     * Approved template message — the only way to start a conversation.
     */
    private function templatePayload(string $phone, string $templateName, array $params): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $phone,
            'type'              => 'template',
            'template'          => [
                'name'     => $templateName,
                'language' => [
                    'code' => Setting::get('whatsapp_language_code', config('services.whatsapp.language_code', 'az')),
                ],
            ],
        ];

        if (!empty($params)) {
            $payload['template']['components'] = [[
                'type'       => 'body',
                'parameters' => array_map(
                    fn ($value) => ['type' => 'text', 'text' => (string) $value],
                    $params
                ),
            ]];
        }

        return $payload;
    }

    /**
     * Log-only driver — used while WhatsApp is disabled or not yet configured.
     *
     * @return array{success: bool, message_id: null, response_body: array}
     */
    private function sendViaLog(string $phone, string $message, ?string $templateName, array $templateParams): array
    {
        $body = [
            'driver'   => 'log',
            'phone'    => $this->normalizePhone($phone),
            'message'  => $message,
            'template' => $templateName,
            'params'   => $templateParams,
            'sent_at'  => now()->toDateTimeString(),
        ];

        Log::channel('single')->info('[WHATSAPP LOG DRIVER]', $body);

        return ['success' => true, 'message_id' => null, 'response_body' => $body];
    }

    private function phoneNumberId(): string
    {
        return trim((string) Setting::get('whatsapp_phone_number_id', config('services.whatsapp.phone_number_id', '')));
    }

    private function accessToken(): string
    {
        $token = (string) Setting::get('whatsapp_access_token', '');

        if ($token !== '') {
            try {
                return trim(decrypt($token));
            } catch (\Exception $e) {
                Log::error('WhatsApp access token could not be decrypted.');
                return '';
            }
        }

        return trim((string) config('services.whatsapp.access_token', ''));
    }

    private function logMessage(
        string  $phone,
        string  $message,
        string  $type,
        string  $status,
        ?int    $doctorId,
        ?int    $appointmentId,
        ?string $messageId,
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
            'channel'        => 'whatsapp',
            'status'         => $status,
            'sent_at'        => $status === 'sent' ? now() : null,
            'receiver_id'    => $messageId,
            'response_body'  => $responseBody,
        ]);

        Log::channel('sms')->info('WHATSAPP ' . strtoupper($status), [
            'phone'          => $phone,
            'type'           => $type,
            'doctor_id'      => $doctorId,
            'appointment_id' => $appointmentId,
            'message_id'     => $messageId,
            'response_body'  => $responseBody,
        ]);
    }
}
