@extends('layouts.doctor')

@section('title', 'SMS Şablonları')
@section('page-title', 'SMS Şablonları')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-chat-dots me-2 text-info"></i>SMS Şablonları
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Boş buraxsanız sistem tərəfindən müəyyən edilmiş defolt şablon istifadə olunacaq.
                </p>

                <form method="POST" action="{{ route('panel.sms-templates.save') }}">
                    @csrf
                    @method('PUT')

                    {{-- Appointment template --}}
                    <div class="mb-4">
                        <label class="form-label fw-medium">
                            <i class="bi bi-calendar-check me-1 text-primary"></i>Randevu Təsdiq SMS
                        </label>
                        <p class="text-muted small mb-2">Randevu yaradıldıqda müştəriyə göndərilir.</p>
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            @foreach(['{ad_soyad}', '{xidmet}', '{tarix}', '{saat}', '{muessise}', '{xerite}'] as $ph)
                                <button type="button" class="btn btn-outline-secondary btn-sm placeholder-btn"
                                        data-target="sms_appointment_template" data-placeholder="{{ $ph }}">{{ $ph }}</button>
                            @endforeach
                        </div>
                        <textarea id="sms_appointment_template"
                                  name="sms_appointment_template"
                                  class="form-control font-monospace @error('sms_appointment_template') is-invalid @enderror"
                                  rows="3" maxlength="160"
                                  placeholder="Boş buraxın — defolt şablon istifadə olunacaq">{{ old('sms_appointment_template', auth()->user()->sms_appointment_template) }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            @error('sms_appointment_template')
                                <div class="text-danger small">{{ $message }}</div>
                            @else
                                <div></div>
                            @enderror
                            <small class="text-muted"><span id="appt-count">0</span>/160</small>
                        </div>
                    </div>

                    {{-- Reminder template --}}
                    <div class="mb-4">
                        <label class="form-label fw-medium">
                            <i class="bi bi-bell me-1 text-warning"></i>Xatırlatma SMS
                        </label>
                        <p class="text-muted small mb-2">Randevudan əvvəl avtomatik göndərilir.</p>
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            @foreach(['{ad_soyad}', '{xidmet}', '{tarix}', '{saat}', '{muessise}'] as $ph)
                                <button type="button" class="btn btn-outline-secondary btn-sm placeholder-btn"
                                        data-target="sms_reminder_template" data-placeholder="{{ $ph }}">{{ $ph }}</button>
                            @endforeach
                        </div>
                        <textarea id="sms_reminder_template"
                                  name="sms_reminder_template"
                                  class="form-control font-monospace @error('sms_reminder_template') is-invalid @enderror"
                                  rows="3" maxlength="160"
                                  placeholder="Boş buraxın — defolt şablon istifadə olunacaq">{{ old('sms_reminder_template', auth()->user()->sms_reminder_template) }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            @error('sms_reminder_template')
                                <div class="text-danger small">{{ $message }}</div>
                            @else
                                <div></div>
                            @enderror
                            <small class="text-muted"><span id="rem-count">0</span>/160</small>
                        </div>
                    </div>

                    {{-- SMS copy to self --}}
                    <div class="mb-4 p-3 border rounded-3 bg-light">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox"
                                       id="sms_copy_to_self" name="sms_copy_to_self" value="1"
                                       {{ auth()->user()->sms_copy_to_self ? 'checked' : '' }}
                                       style="width:1.2em;height:1.2em;cursor:pointer;">
                                <label class="form-check-label fw-medium ms-1" for="sms_copy_to_self" style="cursor:pointer;">
                                    Xatırlatma SMS-nin kopyasını mənə də göndər
                                </label>
                            </div>
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center"
                                    style="width:1.6rem;height:1.6rem;flex-shrink:0;"
                                    data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover focus"
                                    data-bs-content="Bu seçimi aktivləşdirdikdə, xəstəyə göndərilən hər xatırlatma SMS-i sizin telefon nömrənizə də göndəriləcək. Bu, paketinizdəki SMS limitinizə əlavə olaraq sayılacaq.">
                                <i class="bi bi-info-circle text-secondary" style="font-size:.9rem;"></i>
                            </button>
                        </div>
                        <div class="text-muted mt-1 small" style="padding-left:1.85rem;">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                            Bu seçim aktivləşdirilərsə, göndərilən hər xatırlatma SMS-i limitinizə təsir edəcək.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                    </button>
                </form>
            </div>
        </div>

        {{-- Placeholder docs --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-info"></i>Yer Tutucular</h6>
            </div>

            {{-- Mobile --}}
            <div class="d-md-none">
                @foreach([
                    ['{ad_soyad}', 'Xəstənin tam adı',          'Əli Əliyev'],
                    ['{xidmet}',   'Müalicə / xidmət növü',      'Diş müalicəsi'],
                    ['{tarix}',    'Randevu tarixi',              '26.03.2026'],
                    ['{saat}',     'Randevu saatı',               '14:00'],
                    ['{muessise}', 'Müəssisə adı (profildən)',    'DentCare'],
                    ['{xerite}',   'Müəssisənin xəritə linki',   rtrim(config('app.url'), '/').'/map/abc1234'],
                ] as [$ph, $desc, $example])
                <div class="px-3 py-2 border-bottom">
                    <code class="text-primary">{{ $ph }}</code>
                    <div class="text-muted small mt-1">{{ $desc }}</div>
                    <div class="text-secondary small"><i class="bi bi-arrow-right me-1"></i>{{ $example }}</div>
                </div>
                @endforeach
            </div>

            {{-- Desktop --}}
            <div class="card-body p-0 d-none d-md-block">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:150px">Yer tutucu</th>
                            <th>Nəyi əvəz edir</th>
                            <th>Nümunə</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><code>{ad_soyad}</code></td><td>Xəstənin tam adı</td><td class="text-muted">Əli Əliyev</td></tr>
                        <tr><td><code>{xidmet}</code></td><td>Müalicə / xidmət növü</td><td class="text-muted">Diş müalicəsi</td></tr>
                        <tr><td><code>{tarix}</code></td><td>Randevu tarixi</td><td class="text-muted">26.03.2026</td></tr>
                        <tr><td><code>{saat}</code></td><td>Randevu saatı</td><td class="text-muted">14:00</td></tr>
                        <tr><td><code>{muessise}</code></td><td>Müəssisə adı (profildən)</td><td class="text-muted">DentCare</td></tr>
                        <tr><td><code>{xerite}</code></td><td>Müəssisənin xəritə linki (qısa URL)</td><td class="text-muted">{{ rtrim(config('app.url'), '/') }}/map/abc1234</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));

(function () {
    function updateCount(textarea, countEl) {
        countEl.textContent = textarea.value.length;
        countEl.classList.toggle('text-danger', textarea.value.length > 140);
    }
    const apptArea  = document.getElementById('sms_appointment_template');
    const remArea   = document.getElementById('sms_reminder_template');
    const apptCount = document.getElementById('appt-count');
    const remCount  = document.getElementById('rem-count');

    updateCount(apptArea, apptCount);
    updateCount(remArea,  remCount);
    apptArea.addEventListener('input', () => updateCount(apptArea, apptCount));
    remArea.addEventListener('input',  () => updateCount(remArea,  remCount));

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
