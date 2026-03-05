@extends('layouts.admin')

@section('title', 'Defolt SMS Şablonları')
@section('page-title', 'Defolt SMS Şablonları')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-building me-2 text-secondary"></i>Defolt Müəssisə Adı</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    İstifadəçilər öz profillərindən müəssisə adı və SMS şablonu təyin etməyibsə bu defolt dəyərlər istifadə olunur.
                </p>
                <form method="POST" action="{{ route('admin.settings.sms-templates.save') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="default_muessise_adi" class="form-label fw-medium">Müəssisə / Şirkət adı (defolt)</label>
                        <input type="text"
                               id="default_muessise_adi"
                               name="default_muessise_adi"
                               class="form-control @error('default_muessise_adi') is-invalid @enderror"
                               value="{{ old('default_muessise_adi', $defaultMuessise) }}"
                               maxlength="100"
                               required>
                        @error('default_muessise_adi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Appointment template --}}
                    <hr class="my-4">
                    <h6 class="fw-semibold mb-1"><i class="bi bi-calendar-check me-1 text-primary"></i>Randevu Təsdiq SMS</h6>
                    <p class="text-muted small mb-2">Randevu yaradıldıqda müştəriyə göndərilir.</p>

                    <div class="mb-1">
                        <label for="sms_appointment_template" class="form-label fw-medium">Mətn şablonu</label>
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            @foreach(['{ad_soyad}', '{xidmet}', '{tarix}', '{saat}', '{muessise}'] as $ph)
                                <button type="button"
                                        class="btn btn-outline-secondary btn-sm placeholder-btn"
                                        data-target="sms_appointment_template"
                                        data-placeholder="{{ $ph }}">
                                    {{ $ph }}
                                </button>
                            @endforeach
                        </div>
                        <textarea id="sms_appointment_template"
                                  name="sms_appointment_template"
                                  class="form-control font-monospace @error('sms_appointment_template') is-invalid @enderror"
                                  rows="3"
                                  maxlength="160"
                                  required>{{ old('sms_appointment_template', $appointmentTemplate) }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            @error('sms_appointment_template')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div></div>
                            @enderror
                            <small class="text-muted">
                                <span id="appt-count">0</span>/160 simvol
                            </small>
                        </div>
                    </div>

                    {{-- Reminder template --}}
                    <hr class="my-4">
                    <h6 class="fw-semibold mb-1"><i class="bi bi-bell me-1 text-warning"></i>Xatırlatma SMS</h6>
                    <p class="text-muted small mb-2">Randevudan 2 saat əvvəl avtomatik göndərilir.</p>

                    <div class="mb-1">
                        <label for="sms_reminder_template" class="form-label fw-medium">Mətn şablonu</label>
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            @foreach(['{ad_soyad}', '{xidmet}', '{tarix}', '{saat}', '{muessise}'] as $ph)
                                <button type="button"
                                        class="btn btn-outline-secondary btn-sm placeholder-btn"
                                        data-target="sms_reminder_template"
                                        data-placeholder="{{ $ph }}">
                                    {{ $ph }}
                                </button>
                            @endforeach
                        </div>
                        <textarea id="sms_reminder_template"
                                  name="sms_reminder_template"
                                  class="form-control font-monospace @error('sms_reminder_template') is-invalid @enderror"
                                  rows="3"
                                  maxlength="160"
                                  required>{{ old('sms_reminder_template', $reminderTemplate) }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            @error('sms_reminder_template')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div></div>
                            @enderror
                            <small class="text-muted">
                                <span id="rem-count">0</span>/160 simvol
                            </small>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Placeholder documentation --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-info"></i>Yer Tutucular (Placeholders)</h6>
            </div>

            {{-- Mobile: card list --}}
            <div class="d-md-none">
                @foreach([
                    ['{ad_soyad}', 'Müştərinin tam adı',                          'Əli Əliyev'],
                    ['{xidmet}',   'Xidmət növü',                                  'Diş müalicəsi'],
                    ['{tarix}',    'Randevu tarixi (GG.AA.İİİİ)',                   '26.03.2026'],
                    ['{saat}',     'Randevu saatı (SS:DQ)',                         '14:00'],
                    ['{muessise}', 'İstifadəçinin öz müəssisə adı (yoxdursa defolt)', 'ABC Mərkəzi'],
                ] as [$ph, $desc, $example])
                <div class="px-3 py-2 border-bottom">
                    <code class="text-primary">{{ $ph }}</code>
                    <div class="text-muted small mt-1">{{ $desc }}</div>
                    <div class="text-secondary small"><i class="bi bi-arrow-right me-1"></i>{{ $example }}</div>
                </div>
                @endforeach
            </div>

            {{-- Desktop: table --}}
            <div class="card-body p-0 d-none d-md-block">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:160px">Yer tutucu</th>
                            <th>Nəyi əvəz edir</th>
                            <th>Nümunə</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><code>{ad_soyad}</code></td><td>Müştərinin tam adı</td><td class="text-muted">Əli Əliyev</td></tr>
                        <tr><td><code>{xidmet}</code></td><td>Xidmət növü</td><td class="text-muted">Diş müalicəsi</td></tr>
                        <tr><td><code>{tarix}</code></td><td>Randevu tarixi (GG.AA.İİİİ)</td><td class="text-muted">26.03.2026</td></tr>
                        <tr><td><code>{saat}</code></td><td>Randevu saatı (SS:DQ)</td><td class="text-muted">14:00</td></tr>
                        <tr><td><code>{muessise}</code></td><td>İstifadəçinin öz müəssisə adı (yoxdursa defolt)</td><td class="text-muted">ABC Mərkəzi</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-light border-top">
                <p class="mb-1 small text-muted fw-medium">Nümunə şablon:</p>
                <p class="mb-0 small font-monospace text-dark">
                    Hörmətli {ad_soyad}, {tarix} {saat} tarixində {xidmet} xidməti üçün randevunuz təsdiqləndi. Hörmətlə, {muessise}
                </p>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
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
            const targetId    = btn.dataset.target;
            const placeholder = btn.dataset.placeholder;
            const textarea    = document.getElementById(targetId);
            const start       = textarea.selectionStart;
            const before      = textarea.value.substring(0, start);
            const after       = textarea.value.substring(textarea.selectionEnd);

            textarea.value = before + placeholder + after;
            textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
            textarea.focus();
            textarea.dispatchEvent(new Event('input'));
        });
    });
});
</script>
@endpush
