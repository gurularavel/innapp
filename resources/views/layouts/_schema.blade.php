{{-- Site-wide structured data (Organization + WebSite). Page-specific graphs go through @push('schema'). --}}
@php
    $siteUrl = rtrim(config('app.url'), '/');
    $supportPhone = preg_replace('/\D+/', '', (string) config('services.support.whatsapp'));
    $organization = array_filter([
        '@type' => 'Organization',
        '@id' => $siteUrl . '/#organization',
        'name' => 'InnApp',
        'url' => $siteUrl . '/',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => asset('favicon/web-app-manifest-512x512.png'),
            'width' => 512,
            'height' => 512,
        ],
        'email' => 'info@innapp.az',
        'telephone' => $supportPhone ? '+' . $supportPhone : null,
        'sameAs' => array_values(array_filter(config('services.social', []))) ?: null,
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Bakı',
            'addressCountry' => 'AZ',
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'contactType' => 'customer support',
            'telephone' => $supportPhone ? '+' . $supportPhone : null,
            'email' => 'info@innapp.az',
            'availableLanguage' => ['az'],
        ],
    ], fn ($value) => $value !== null);

    $siteSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            $organization,
            [
                '@type' => 'WebSite',
                '@id' => $siteUrl . '/#website',
                'name' => 'InnApp',
                'url' => $siteUrl . '/',
                'inLanguage' => 'az',
                'publisher' => ['@id' => $siteUrl . '/#organization'],
            ],
        ],
    ];
@endphp
<script @cspNonce type="application/ld+json">@json($siteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
