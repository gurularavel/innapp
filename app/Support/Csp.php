<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * The Content Security Policy and the per-request nonce that carries it.
 *
 * Every `<script>` in the app — inline block or CDN tag — is marked with
 * `@cspNonce`, so `script-src` can be a nonce instead of a host list: an
 * injected `<script>` has no nonce and does not run, which is what stops a
 * stored XSS from becoming account takeover.
 *
 * `'strict-dynamic'` lets a script we trusted load its own helpers (gtag and
 * Turnstile both do), so no CDN needs to be named. `https:` and
 * `'unsafe-inline'` sit behind it as fallbacks that newer browsers ignore —
 * they only keep the page working on a browser too old for nonces.
 *
 * `style-src` deliberately has no nonce: the views carry ~270 `style="…"`
 * attributes, which a nonce cannot cover, and a nonce in `style-src` would
 * switch `'unsafe-inline'` off and strip the layout. Script injection is the
 * risk worth closing; style injection is not worth breaking every page for.
 */
class Csp
{
    public const MODE_OFF     = 'off';
    public const MODE_REPORT  = 'report';
    public const MODE_ENFORCE = 'enforce';

    public const MODES = [
        self::MODE_ENFORCE => 'Tam aktiv (bloklayır)',
        self::MODE_REPORT  => 'Yalnız hesabat (bloklamır)',
        self::MODE_OFF     => 'Söndürülüb',
    ];

    public const REPORT_PATH = '/csp-report';

    private ?string $nonce = null;

    /** One random nonce per request, generated the first time a view asks. */
    public function nonce(): string
    {
        return $this->nonce ??= Str::random(24);
    }

    /**
     * Start a new nonce. The middleware calls this as each request begins, so a
     * container that survives between requests (Octane, or a test run) can
     * never serve the same nonce twice — a reused nonce is a reusable
     * injection point.
     */
    public function rotate(): void
    {
        $this->nonce = null;
    }

    /** What `@cspNonce` renders into a script tag. */
    public function attribute(): string
    {
        return 'nonce="' . e($this->nonce()) . '"';
    }

    public function mode(): string
    {
        $mode = (string) Setting::get('csp_mode', self::MODE_ENFORCE);

        return isset(self::MODES[$mode]) ? $mode : self::MODE_ENFORCE;
    }

    /** The header this mode writes to, or null while the policy is off. */
    public function headerName(): ?string
    {
        return match ($this->mode()) {
            self::MODE_ENFORCE => 'Content-Security-Policy',
            self::MODE_REPORT  => 'Content-Security-Policy-Report-Only',
            default            => null,
        };
    }

    public function policy(): string
    {
        $directives = [
            'default-src'     => ["'self'"],
            'base-uri'        => ["'self'"],
            'object-src'      => ["'none'"],
            'frame-ancestors' => ["'self'"],
            'form-action'     => ["'self'"],

            // Nonce first; the rest is the compatibility tail described above.
            'script-src' => ["'nonce-{$this->nonce()}'", "'strict-dynamic'", 'https:', "'unsafe-inline'"],

            'style-src'   => ["'self'", "'unsafe-inline'", 'https://cdn.jsdelivr.net', 'https://fonts.googleapis.com', 'https://fonts.bunny.net'],
            'font-src'    => ["'self'", 'data:', 'https://cdn.jsdelivr.net', 'https://fonts.gstatic.com', 'https://fonts.bunny.net'],
            'img-src'     => ["'self'", 'data:', 'blob:', 'https:'],
            'media-src'   => ["'self'", 'data:'],
            'worker-src'  => ["'self'", 'blob:'],

            // Analytics beacons; everything else talks to our own origin.
            'connect-src' => ["'self'", 'https://*.google-analytics.com', 'https://*.analytics.google.com', 'https://*.googletagmanager.com'],

            // Cloudflare Turnstile renders its challenge in an iframe.
            'frame-src'   => ["'self'", 'https://challenges.cloudflare.com'],

            'report-uri'  => [self::REPORT_PATH],
            'report-to'   => ['csp'],
        ];

        if (app()->environment('production')) {
            $directives['upgrade-insecure-requests'] = [];
        }

        return collect($directives)
            ->map(fn (array $values, string $name) => trim($name . ' ' . implode(' ', $values)))
            ->implode('; ');
    }
}
