<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        $this->configureMailFromDb();
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
