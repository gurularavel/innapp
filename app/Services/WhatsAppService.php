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
 * Credentials come from one of two places, in this order:
 *   1. the clinic's own connection (Panel » WhatsApp bağlantısı),
 *   2. the platform-wide one the admin configured (Ayarlar » WhatsApp),
 * with the .env values acting as the last fallback for the admin connection.
 *
 * A connection is taken as a whole — number id, token, language and the
 * approved template names always come from the same source, because a template
 * only exists on the WhatsApp Business Account it was approved on.
 */
class WhatsAppService
{
    use NormalizesPhone;

    /** Message types that can carry an approved template name. */
    public const TEMPLATE_TYPES = ['appointment', 'reminder', 'birthday', 'holiday'];

    public function __construct(private MessageBuilder $builder) {}

    /**
     * The connection that will actually be used for this clinic.
     *
     * `source` is `clinic` when the clinic connected its own number, `admin`
     * otherwise — callers use it to tell the owner where the settings live.
     *
     * @return array{source: string, enabled: bool, phone_number_id: string, access_token: string, api_version: string, language_code: string}
     */
    public function configFor(?Clinic $clinic): array
    {
        if ($clinic?->hasOwnWhatsapp()) {
            $token = $this->decryptToken($clinic->whatsapp_access_token, "clinic #{$clinic->id}");

            // A token that cannot be read is treated as no connection at all, so
            // the clinic falls back to the admin one instead of going quiet.
            if ($token !== '') {
                return [
                    'source'          => 'clinic',
                    'enabled'         => true,
                    'phone_number_id' => trim((string) $clinic->whatsapp_phone_number_id),
                    'access_token'    => $token,
                    'api_version'     => $this->platformApiVersion(),
                    'language_code'   => trim((string) $clinic->whatsapp_language_code) ?: $this->platformLanguageCode(),
                ];
            }
        }

        return [
            'source'          => 'admin',
            'enabled'         => Setting::get('whatsapp_enabled', '0') === '1',
            'phone_number_id' => trim((string) Setting::get('whatsapp_phone_number_id', config('services.whatsapp.phone_number_id', ''))),
            'access_token'    => $this->platformAccessToken(),
            'api_version'     => $this->platformApiVersion(),
            'language_code'   => $this->platformLanguageCode(),
        ];
    }

    /**
     * WhatsApp is usable for this clinic when either its own connection or the
     * platform-wide one is switched on and complete.
     */
    public function isConfiguredFor(?Clinic $clinic): bool
    {
        return $this->isUsable($this->configFor($clinic));
    }

    /**
     * The platform-wide connection only — what the admin screen and the test
     * command report on.
     */
    public function isConfigured(): bool
    {
        return $this->isConfiguredFor(null);
    }

    /** Approved template name for one message type, from the winning connection. */
    public function templateFor(?Clinic $clinic, string $type): string
    {
        return trim($this->fromWinningSource($clinic, "whatsapp_{$type}_template"));
    }

    /** Placeholder order that fills the template body, from the winning connection. */
    public function paramListFor(?Clinic $clinic, string $type): string
    {
        return trim($this->fromWinningSource($clinic, "whatsapp_{$type}_params"));
    }

    /**
     * Messaging is unlimited under seat based pricing, so there is no quota to
     * check or decrement here — only delivery and logging.
     */
    public function send(
        string          $phone,
        string          $message,
        ?int            $doctorId = null,
        string          $type = 'custom',
        ?int            $appointmentId = null,
        ?string         $templateName = null,
        array           $templateParams = [],
        Clinic|int|null $clinic = null,
        array           $context = []
    ): bool {
        $clinic = $this->resolveClinic($clinic);
        $config = $this->configFor($clinic);

        // Demo clinics never reach the gateway — their data looks real but is not.
        $live = $this->isUsable($config) && ! $clinic?->isDemo();

        ['success' => $success, 'message_id' => $messageId, 'response_body' => $responseBody] =
            $live
                ? $this->sendViaCloudApi($config, $phone, $message, $templateName, $templateParams)
                : $this->sendViaLog($phone, $message, $templateName, $templateParams);

        $status = $success ? 'sent' : 'failed';
        $this->logMessage($phone, $message, $type, $status, $doctorId, $appointmentId, $messageId, $responseBody, $clinic?->id, $context);

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
        $templateName = $this->templateFor($clinic, $type);

        return $this->send(
            $patient->phone,
            $message,
            null,
            $type,
            null,
            $templateName ?: null,
            $templateName !== '' ? $templateParams : [],
            $clinic,
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
        $clinic       = $appointment->clinic;
        $message      = $this->builder->build("sms_{$type}_template", $appointment);
        $templateName = $this->templateFor($clinic, $type);

        $params = $templateName !== ''
            ? $this->builder->orderedParams($this->paramListFor($clinic, $type), $appointment)
            : [];

        return $this->send(
            $appointment->patient->phone,
            $message,
            $appointment->doctor_id,
            $type,
            $appointment->id,
            $templateName ?: null,
            $params,
            $clinic ?? $appointment->clinic_id
        );
    }

    /** A connection can send only when it is switched on and complete. */
    private function isUsable(array $config): bool
    {
        return $config['enabled']
            && $config['phone_number_id'] !== ''
            && $config['access_token'] !== '';
    }

    /**
     * Template names and parameter order belong to the connection that sends the
     * message, so both are read from whichever source won in `configFor()`.
     */
    private function fromWinningSource(?Clinic $clinic, string $key): string
    {
        return $this->configFor($clinic)['source'] === 'clinic'
            ? (string) ($clinic->{$key} ?? '')
            : (string) Setting::get($key, '');
    }

    private function resolveClinic(Clinic|int|null $clinic): ?Clinic
    {
        if ($clinic instanceof Clinic || $clinic === null) {
            return $clinic;
        }

        return Clinic::find($clinic);
    }

    /**
     * @param  array{source: string, phone_number_id: string, access_token: string, api_version: string, language_code: string}  $config
     * @return array{success: bool, message_id: string|null, response_body: array|null}
     */
    private function sendViaCloudApi(array $config, string $phone, string $message, ?string $templateName, array $templateParams): array
    {
        $phone = $this->normalizePhone($phone);
        $url   = "https://graph.facebook.com/{$config['api_version']}/{$config['phone_number_id']}/messages";

        $payload = $templateName
            ? $this->templatePayload($phone, $templateName, $templateParams, $config['language_code'])
            : $this->textPayload($phone, $message);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $config['access_token'],
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
                'phone'  => $phone,
                'source' => $config['source'],
                'status' => $response->status(),
                'error'  => $body['error']['message'] ?? null,
                'code'   => $body['error']['code'] ?? null,
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
    private function templatePayload(string $phone, string $templateName, array $params, string $languageCode): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $phone,
            'type'              => 'template',
            'template'          => [
                'name'     => $templateName,
                'language' => ['code' => $languageCode],
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

    private function platformApiVersion(): string
    {
        return (string) Setting::get('whatsapp_api_version', config('services.whatsapp.api_version', 'v21.0'));
    }

    private function platformLanguageCode(): string
    {
        return (string) Setting::get('whatsapp_language_code', config('services.whatsapp.language_code', 'az'));
    }

    private function platformAccessToken(): string
    {
        $token = $this->decryptToken((string) Setting::get('whatsapp_access_token', ''), 'the platform connection');

        return $token !== '' ? $token : trim((string) config('services.whatsapp.access_token', ''));
    }

    /** Tokens are stored encrypted; one that cannot be read counts as "not set". */
    private function decryptToken(?string $stored, string $owner): string
    {
        if (blank($stored)) {
            return '';
        }

        try {
            return trim(decrypt($stored));
        } catch (\Exception $e) {
            Log::error("WhatsApp access token for {$owner} could not be decrypted.");

            return '';
        }
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
