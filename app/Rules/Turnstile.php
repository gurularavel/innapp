<?php

namespace App\Rules;

use App\Services\TurnstileService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates the token Cloudflare's Turnstile widget posts with the form.
 *
 * Passes silently when Turnstile is switched off, so the rule can sit on a
 * form permanently and the admin decides whether it does anything.
 */
class Turnstile implements ValidationRule
{
    public function __construct(private ?string $ip = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $turnstile = app(TurnstileService::class);

        if (! $turnstile->enabled()) {
            return;
        }

        if (! $turnstile->verify(is_string($value) ? $value : null, $this->ip)) {
            $fail('Təhlükəsizlik yoxlaması keçmədi. Zəhmət olmasa səhifəni yeniləyib yenidən cəhd edin.');
        }
    }
}
