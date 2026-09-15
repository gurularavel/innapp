<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Canonical/OG URLs and JSON-LD @ids must not vary with the request host or scheme.
        // The root URL is only pinned when APP_URL is a real https origin, so a stale
        // localhost value in .env cannot break every generated link.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');

            if (str_starts_with((string) config('app.url'), 'https://')) {
                URL::forceRootUrl(config('app.url'));
            }
        }

        Paginator::useBootstrapFive();
        $this->configureMailFromDb();
        $this->localisePasswordResetMail();
    }

    /**
     * Laravel's stock reset e-mail is English; render ours from an
     * Azerbaijani template instead. The notification class itself is kept
     * (it is what the password broker sends and what tests assert on).
     */
    private function localisePasswordResetMail(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Şifrə sıfırlama — ' . config('app.name'))
                ->view('emails.password-reset', [
                    'user'   => $notifiable,
                    'url'    => $url,
                    'expire' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60),
                ]);
        });
    }

    private function configureMailFromDb(): void
    {
        try {
            $host = Setting::get('smtp_host', '');

            if (empty($host)) {
                return;
            }

            $encryption = Setting::get('smtp_encryption', 'tls');
            $password   = Setting::get('smtp_password', '');

            if ($password) {
                try {
                    $password = decrypt($password);
                } catch (\Exception) {
                    $password = '';
                }
            }

            // Laravel 12 uses 'scheme' for SMTP: 'smtps' for SSL, null for TLS/STARTTLS
            $scheme = $encryption === 'ssl' ? 'smtps' : null;

            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host',     $host);
            Config::set('mail.mailers.smtp.port',     (int) Setting::get('smtp_port', 587));
            Config::set('mail.mailers.smtp.scheme',   $scheme);
            Config::set('mail.mailers.smtp.username', Setting::get('smtp_username', ''));
            Config::set('mail.mailers.smtp.password', $password ?: null);
            Config::set('mail.from.address',          Setting::get('smtp_from_address', config('mail.from.address')));
            Config::set('mail.from.name',             Setting::get('smtp_from_name',    config('mail.from.name')));
        } catch (\Exception) {
            // DB not ready yet (e.g. during migrations) — silently skip
        }
    }
}
