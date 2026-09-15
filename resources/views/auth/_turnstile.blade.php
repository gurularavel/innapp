{{-- Cloudflare Turnstile — renders nothing while the check is switched off. --}}
@php $turnstile = app(\App\Services\TurnstileService::class); @endphp

@if($turnstile->enabledFor($form))
    <div class="mb-3">
        <div class="cf-turnstile"
             data-sitekey="{{ $turnstile->siteKey() }}"
             data-theme="light"
             data-retry="auto"></div>
        @error(\App\Services\TurnstileService::FIELD)
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif
