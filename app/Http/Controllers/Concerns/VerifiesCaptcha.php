<?php

namespace App\Http\Controllers\Concerns;

use App\Rules\Turnstile;
use App\Services\TurnstileService;
use Illuminate\Http\Request;

/**
 * Adds the Cloudflare Turnstile check to a public form.
 *
 * Merge `captchaRules()` into the controller's own validation array. While
 * Turnstile is switched off the array is empty and the form behaves exactly as
 * before; once it is on, a missing token fails the request outright — a bot
 * that simply omits the field must not slip through.
 */
trait VerifiesCaptcha
{
    /** @return array<string, array<int, mixed>> */
    protected function captchaRules(Request $request, string $form): array
    {
        if (! app(TurnstileService::class)->enabledFor($form)) {
            return [];
        }

        return [
            TurnstileService::FIELD => ['required', new Turnstile($request->ip())],
        ];
    }

    /** @return array<string, string> */
    protected function captchaMessages(): array
    {
        return [
            TurnstileService::FIELD . '.required' => 'Zəhmət olmasa robot olmadığınızı təsdiqləyin.',
        ];
    }
}
