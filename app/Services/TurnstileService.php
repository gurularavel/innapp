<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cloudflare Turnstile — the bot check in front of the public sign-up forms.
 *
 * Keys are admin-tunable (Ayarlar » Təhlükəsizlik), with the .env values as a
 * fallback. The secret is stored encrypted, like every other credential in
 * `settings`.
 *
 * The check is fail-closed: once the admin switches it on, a form that cannot
 * be verified is rejected. An unconfigured or switched-off Turnstile lets every
 * request through, so nothing breaks in dev.
 */
class TurnstileService
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /** The field Cloudflare's widget posts back with the form. */
    public const FIELD = 'cf-turnstile-response';

    /**
     * The public forms the check can guard, and whether each one is on by
     * default. Login is off by default — it is the form real customers use
     * most and Laravel already rate-limits it.
     *
     * @var array<string, array{label: string, hint: string, default: bool}>
     */
    public const FORMS = [
        'register' => [
            'label'   => 'Müəssisə qeydiyyatı',
            'hint'    => '/register — əsas spam mənbəyi',
            'default' => true,
        ],
        'promoter' => [
            'label'   => 'Promotor (referal) qeydiyyatı',
            'hint'    => '/promoter/register',
            'default' => true,
        ],
        'demo' => [
            'label'   => 'Demo hesab yaratmaq',
            'hint'    => '/demo — hər klikdə yeni klinika yaradır',
            'default' => true,
        ],
        'password' => [
            'label'   => 'Şifrə bərpası',
            'hint'    => '/forgot-password — e-poçt göndərir',
            'default' => true,
        ],
        'login' => [
            'label'   => 'Giriş',
            'hint'    => '/login — real istifadəçiləri yavaşladır, ehtiyatla',
            'default' => false,
        ],
    ];

    /** True only when the admin switched it on and both keys are present. */
    public function enabled(): bool
    {
        return Setting::get('turnstile_enabled', '0') === '1'
            && $this->siteKey() !== ''
            && $this->secretKey() !== '';
    }

    /** Whether the check guards this particular form. */
    public function enabledFor(string $form): bool
    {
        if (! $this->enabled() || ! isset(self::FORMS[$form])) {
            return false;
        }

        return Setting::get(
            self::settingKey($form),
            self::FORMS[$form]['default'] ? '1' : '0'
        ) === '1';
    }

    public static function settingKey(string $form): string
    {
        return 'turnstile_on_' . $form;
    }

    public function siteKey(): string
    {
        return trim((string) Setting::get('turnstile_site_key', (string) config('services.turnstile.site_key', '')));
    }

    public function secretKey(): string
    {
        $stored = Setting::get('turnstile_secret_key');

        if (blank($stored)) {
            return trim((string) config('services.turnstile.secret_key', ''));
        }

        try {
            return trim(decrypt($stored));
        } catch (\Exception $e) {
            Log::error('Turnstile secret key could not be decrypted.');

            return '';
        }
    }

    /**
     * Ask Cloudflare whether this token is a real, unused human solve.
     *
     * A network failure counts as a failure — the whole point of the check is
     * to stop automated sign-ups, so it must not open up when Cloudflare is
     * unreachable.
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post(self::VERIFY_URL, array_filter([
                    'secret'   => $this->secretKey(),
                    'response' => $token,
                    'remoteip' => $ip,
                ]));

            if (! $response->successful()) {
                Log::warning('Turnstile verification request failed.', ['status' => $response->status()]);

                return false;
            }

            $body = $response->json();

            if (! ($body['success'] ?? false)) {
                Log::info('Turnstile rejected a submission.', ['errors' => $body['error-codes'] ?? []]);

                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Turnstile verification threw: ' . $e->getMessage());

            return false;
        }
    }
}
