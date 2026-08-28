<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The clinic's own WhatsApp Cloud API connection.
 *
 * Until a clinic connects its own number, messages go out over the platform-wide
 * connection the admin set up — this screen is what lets an owner send from their
 * own WhatsApp Business number instead. Everything here is clinic-wide, so it is
 * owner-only, enforced in the controller like the rest of the clinic settings.
 */
class WhatsappController extends Controller
{
    public function __construct(private WhatsAppService $whatsapp) {}

    public function edit()
    {
        $clinic = $this->authorizedClinic();
        $config = $this->whatsapp->configFor($clinic);

        return view('doctor.whatsapp.edit', [
            'clinic'        => $clinic,
            'config'        => $config,
            'usingOwn'      => $config['source'] === 'clinic',
            'hasToken'      => filled($clinic->whatsapp_access_token),
            'platformReady' => $this->whatsapp->isConfigured(),
            'whatsappReady' => $this->whatsapp->isConfiguredFor($clinic),
            'templateTypes' => $this->templateLabels(),
        ]);
    }

    public function update(Request $request)
    {
        $clinic = $this->authorizedClinic();

        $request->validate([
            'whatsapp_enabled'         => ['boolean'],
            'whatsapp_number'          => ['nullable', 'string', 'max:20'],
            'whatsapp_phone_number_id' => ['nullable', 'string', 'max:64'],
            'whatsapp_access_token'    => ['nullable', 'string', 'max:1000'],
            'whatsapp_language_code'   => ['nullable', 'string', 'max:10'],
            'whatsapp_appointment_template' => ['nullable', 'string', 'max:100'],
            'whatsapp_appointment_params'   => ['nullable', 'string', 'max:255'],
            'whatsapp_reminder_template'    => ['nullable', 'string', 'max:100'],
            'whatsapp_reminder_params'      => ['nullable', 'string', 'max:255'],
            'whatsapp_birthday_template'    => ['nullable', 'string', 'max:100'],
            'whatsapp_birthday_params'      => ['nullable', 'string', 'max:255'],
            'whatsapp_holiday_template'     => ['nullable', 'string', 'max:100'],
            'whatsapp_holiday_params'       => ['nullable', 'string', 'max:255'],
        ]);

        $enabled  = $request->boolean('whatsapp_enabled');
        $hasToken = $request->filled('whatsapp_access_token') || filled($clinic->whatsapp_access_token);

        // Half a connection is worse than none: it would look active and send nothing.
        if ($enabled && (! $request->filled('whatsapp_phone_number_id') || ! $hasToken)) {
            return back()->withInput()->withErrors([
                'whatsapp_enabled' => 'Öz WhatsApp bağlantınızı aktivləşdirmək üçün Phone Number ID və Access Token doldurulmalıdır.',
            ]);
        }

        $data = [
            'whatsapp_enabled'         => $enabled,
            'whatsapp_number'          => $request->whatsapp_number ?: null,
            'whatsapp_phone_number_id' => $request->whatsapp_phone_number_id ?: null,
            'whatsapp_language_code'   => $request->whatsapp_language_code ?: null,
        ];

        foreach (array_keys($this->templateLabels()) as $type) {
            $data["whatsapp_{$type}_template"] = $request->input("whatsapp_{$type}_template") ?: null;
            $data["whatsapp_{$type}_params"]   = $request->input("whatsapp_{$type}_params") ?: null;
        }

        // Only overwrite the token when a new one is typed in — it is never shown back.
        if ($request->filled('whatsapp_access_token')) {
            $data['whatsapp_access_token'] = encrypt(trim($request->whatsapp_access_token));
        }

        $clinic->update($data);

        return redirect()->route('panel.whatsapp.edit')
            ->with('success', 'WhatsApp bağlantısı yadda saxlandı.')
            ->with($this->channelWarning($clinic->fresh()));
    }

    /**
     * Forget the clinic's credentials entirely, so it falls back to the
     * platform-wide connection.
     */
    public function disconnect()
    {
        $clinic = $this->authorizedClinic();

        $clinic->update([
            'whatsapp_enabled'         => false,
            'whatsapp_phone_number_id' => null,
            'whatsapp_access_token'    => null,
        ]);

        return redirect()->route('panel.whatsapp.edit')
            ->with('success', 'WhatsApp bağlantısı silindi.')
            ->with($this->channelWarning($clinic->fresh()));
    }

    /**
     * Send one free-form message over whichever connection this clinic would
     * really use, so the owner can see the credentials work.
     *
     * Free-form text only reaches a number that wrote to the business in the
     * last 24 hours — outside that window Meta accepts the request but the
     * message is not delivered, which is what the hint on the screen says.
     */
    public function test(Request $request)
    {
        $clinic = $this->authorizedClinic();

        $request->validate([
            'test_phone' => ['required', 'string', 'max:20'],
        ]);

        if (! $this->whatsapp->isConfiguredFor($clinic)) {
            return back()->with('error', 'WhatsApp hazırda aktiv deyil — nə sizin bağlantınız, nə də sistem bağlantısı qurulub.');
        }

        $sent = $this->whatsapp->send(
            $request->test_phone,
            'Test mesajı — ' . ($clinic->name ?: config('app.name')),
            Auth::id(),
            'custom',
            null,
            null,
            [],
            $clinic
        );

        return back()->with(
            $sent ? 'success' : 'error',
            $sent
                ? 'Test mesajı göndərildi. Çatmadısa, nömrənin son 24 saat ərzində sizə yazdığını yoxlayın.'
                : 'Test mesajı göndərilmədi. Phone Number ID və Access Token-i yoxlayın.'
        );
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function authorizedClinic(): Clinic
    {
        $user = Auth::user();

        abort_unless($user->canManageClinic(), 403, 'WhatsApp bağlantısını yalnız müəssisə sahibi qura bilər.');

        $clinic = $user->clinic;

        abort_unless($clinic, 404);

        return $clinic;
    }

    /**
     * Warn when the clinic prefers WhatsApp but nothing can send it — the
     * messages still go out over SMS, silently, and the owner should know.
     *
     * @return array<string, string>
     */
    private function channelWarning(Clinic $clinic): array
    {
        if ($clinic->notify_channel === 'sms' || $this->whatsapp->isConfiguredFor($clinic)) {
            return [];
        }

        return ['warning' => 'Bildiriş kanalı WhatsApp seçilib, amma aktiv bağlantı yoxdur — mesajlar SMS ilə göndəriləcək.'];
    }

    /** @return array<string, string> Message type => Azerbaijani label */
    private function templateLabels(): array
    {
        return [
            'appointment' => 'Randevu təsdiqi',
            'reminder'    => 'Xatırlatma',
            'birthday'    => 'Ad günü',
            'holiday'     => 'Bayram',
        ];
    }
}
