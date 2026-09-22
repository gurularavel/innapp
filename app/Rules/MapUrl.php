<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A clinic's map link. It is published through the public `/map/{code}`
 * redirect, so only https links to known map services are accepted —
 * otherwise the short link would work as an open redirector for phishing.
 */
class MapUrl implements ValidationRule
{
    public const HOSTS = [
        'google.com', 'maps.google.com', 'goo.gl', 'maps.app.goo.gl', 'g.page', 'g.co',
        'yandex.com', 'yandex.az', 'yandex.ru', 'maps.yandex.com', 'maps.yandex.az', 'maps.yandex.ru', 'yandex.com.tr',
        'waze.com', 'ul.waze.com',
        'openstreetmap.org', 'osm.org',
        'apple.com', 'maps.apple.com',
        '2gis.az', '2gis.com', 'go.2gis.com',
        'gomap.az',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! self::allowed($value)) {
            $fail('Xəritə linki yalnız Google Maps, Yandex, Waze, Apple, 2GIS və ya GoMap ünvanı ola bilər (https://...).');
        }
    }

    public static function allowed(?string $url): bool
    {
        if (blank($url) || strlen($url) > 2000) {
            return false;
        }

        $parts = parse_url($url);

        if (! is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            return false;
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        $host = strtolower($parts['host']);

        foreach (self::HOSTS as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.' . $allowed)) {
                return true;
            }
        }

        return false;
    }
}
