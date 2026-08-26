@extends('layouts.admin')

@section('title', 'Təbrik Şablonları')
@section('page-title', 'Təbrik Şablonları')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Adoption --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row text-center g-3">
                    <div class="col-4">
                        <div class="fs-4 fw-semibold">{{ $usage['clinics'] }}</div>
                        <div class="text-muted small">Aktiv klinika</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-4 fw-semibold text-warning">{{ $usage['birthday'] }}</div>
                        <div class="text-muted small">Ad günü təbriki açıq</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-4 fw-semibold text-success">{{ $usage['holiday'] }}</div>
                        <div class="text-muted small">Bayram təbriki açıq</div>
                    </div>
                </div>
                <div class="text-muted small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Təbriklər hər klinikada defolt olaraq <strong>sönülüdür</strong> — klinika özü aktivləşdirənə qədər
                    heç bir müştəriyə mesaj getmir. Buradakı mətnlər klinika öz şablonunu yazmayana qədər işlədilir.
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-gift me-2 text-danger"></i>Defolt Təbrik Mətnləri</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.greetings.save') }}">
                    @csrf
                    @method('PUT')

                    {{-- Send hour --}}
                    <div class="mb-4">
                        <label for="greetings_send_hour" class="form-label fw-medium">
                            <i class="bi bi-clock me-1 text-primary"></i>Göndərilmə saatı
                        </label>
                        <select id="greetings_send_hour"
                                name="greetings_send_hour"
                                class="form-select @error('greetings_send_hour') is-invalid @enderror"
                                style="max-width: 160px;">
                            @for($hour = 0; $hour < 24; $hour++)
                                <option value="{{ $hour }}"
                                    {{ (int) old('greetings_send_hour', $settings['greetings_send_hour']) === $hour ? 'selected' : '' }}>
                                    {{ sprintf('%02d:00', $hour) }}
                                </option>
                            @endfor
                        </select>
                        <div class="form-text">
                            Bütün klinikalar üçün eynidir. Cron hər saat işləyir, təbriklər isə yalnız bu saatda göndərilir.
                        </div>
                        @error('greetings_send_hour')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    {{-- Birthday --}}
                    <h6 class="fw-semibold mb-1"><i class="bi bi-balloon me-1 text-danger"></i>Ad Günü Təbriki</h6>
                    <p class="text-muted small mb-2">
                        Müştərinin doğum tarixi bu günə düşdükdə göndərilir. Doğum tarixi qeyd edilməyən müştərilərə mesaj getmir.
                    </p>

                    <div class="mb-4">
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            @foreach(\App\Services\MessageBuilder::BIRTHDAY_PLACEHOLDERS as $ph)
                                <button type="button" class="btn btn-outline-secondary btn-sm placeholder-btn"
                                        data-target="sms_birthday_template" data-placeholder="{{ $ph }}">{{ $ph }}</button>
                            @endforeach
                        </div>
                        <textarea id="sms_birthday_template"
                                  name="sms_birthday_template"
                                  class="form-control font-monospace @error('sms_birthday_template') is-invalid @enderror"
                                  rows="3" maxlength="160" required>{{ old('sms_birthday_template', $settings['sms_birthday_template']) }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            @error('sms_birthday_template')
                                <div class="text-danger small">{{ $message }}</div>
                            @else
                                <div></div>
                            @enderror
                            <small class="text-muted"><span data-counter-for="sms_birthday_template">0</span>/160</small>
                        </div>
                    </div>

                    {{-- Holiday --}}
                    <h6 class="fw-semibold mb-1"><i class="bi bi-calendar-heart me-1 text-success"></i>Bayram Təbriki</h6>
                    <p class="text-muted small mb-2">
                        Bayram təqvimindəki tarixlərdə göndərilir. Ayrı-ayrı bayramlar üçün fərqli mətn yazmaq istəsəniz,
                        <a href="{{ route('admin.holidays.index') }}">Bayram Təqvimi</a> bölməsindən həmin bayrama öz mətnini əlavə edin.
                    </p>

                    <div class="mb-4">
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            @foreach(\App\Services\MessageBuilder::HOLIDAY_PLACEHOLDERS as $ph)
                                <button type="button" class="btn btn-outline-secondary btn-sm placeholder-btn"
                                        data-target="sms_holiday_template" data-placeholder="{{ $ph }}">{{ $ph }}</button>
                            @endforeach
                        </div>
                        <textarea id="sms_holiday_template"
                                  name="sms_holiday_template"
                                  class="form-control font-monospace @error('sms_holiday_template') is-invalid @enderror"
                                  rows="3" maxlength="160" required>{{ old('sms_holiday_template', $settings['sms_holiday_template']) }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            @error('sms_holiday_template')
                                <div class="text-danger small">{{ $message }}</div>
                            @else
                                <div></div>
                            @enderror
                            <small class="text-muted"><span data-counter-for="sms_holiday_template">0</span>/160</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                    </button>
                </form>
            </div>
        </div>

        {{-- Placeholder docs --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-info"></i>Yer Tutucular</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:150px">Yer tutucu</th>
                            <th>Nəyi əvəz edir</th>
                            <th>Nümunə</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><code>{ad}</code></td><td>Müştərinin adı</td><td class="text-muted">Nigar</td></tr>
                        <tr><td><code>{ad_soyad}</code></td><td>Müştərinin tam adı</td><td class="text-muted">Nigar Əliyeva</td></tr>
                        <tr><td><code>{yas}</code></td><td>Yaşı — yalnız ad günü mətnində</td><td class="text-muted">34</td></tr>
                        <tr><td><code>{bayram}</code></td><td>Bayramın adı — yalnız bayram mətnində</td><td class="text-muted">Novruz bayramı</td></tr>
                        <tr><td><code>{muessise}</code></td><td>Klinikanın adı</td><td class="text-muted">DentCare</td></tr>
                        <tr><td><code>{xerite}</code></td><td>Klinikanın xəritə linki (qısa URL)</td><td class="text-muted">{{ rtrim(config('app.url'), '/') }}/map/abc1234</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Holiday calendar shortcut --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="fw-semibold"><i class="bi bi-calendar-heart me-1 text-success"></i>Bayram Təqvimi</div>
                    <div class="text-muted small">Hazırda {{ $holidays->count() }} aktiv bayram var.</div>
                </div>
                <a href="{{ route('admin.holidays.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>Təqvimi idarə et
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('[data-counter-for]').forEach(function (counter) {
        const area = document.getElementById(counter.dataset.counterFor);
        if (!area) return;

        function update() {
            counter.textContent = area.value.length;
            counter.classList.toggle('text-danger', area.value.length > 140);
        }

        update();
        area.addEventListener('input', update);
    });

    document.querySelectorAll('.placeholder-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const ta = document.getElementById(btn.dataset.target);
            const ph = btn.dataset.placeholder;
            const s  = ta.selectionStart;
            ta.value = ta.value.substring(0, s) + ph + ta.value.substring(ta.selectionEnd);
            ta.selectionStart = ta.selectionEnd = s + ph.length;
            ta.focus();
            ta.dispatchEvent(new Event('input'));
        });
    });
})();
</script>
@endpush
