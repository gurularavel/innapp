@php($gaMeasurementId = config('services.google_analytics.measurement_id'))
@if ($gaMeasurementId)
    {{-- Google tag (gtag.js) --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaMeasurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', '{{ $gaMeasurementId }}');
    </script>
@endif
