@extends('layouts.doctor')

@section('title', 'Bildiriş Ayarları')
@section('page-title', 'Bildiriş Ayarları')

@section('content')
@php
    $currentChannel = old('notify_channel', $clinic->notify_channel ?? 'sms');
    $canEdit        = auth()->user()->canManageClinic();
@endphp
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-chat-dots me-2 text-info"></i>Bildiriş Ayarları
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Müştərilərinizə göndərilən mesajın kanalını və mətnini buradan idarə edirsiniz.
                    Bu ayarlar bütün klinika üçün ortaqdır — şablonu boş buraxsanız sistem defoltu istifadə olunacaq.
                </p>

                @unless($canEdit)
                    <div class="alert alert-secondary small py-2 px-3">
                        <i class="bi bi-lock me-1"></i>
                        Bu ayarları yalnız klinika sahibi dəyişə bilər.
                    </div>
                @endunless

                <form method="POST" action="{{ route('panel.sms-templates.save') }}">
                    @csrf
                    @method('PUT')

                    {{-- Channel selection --}}
                    <div class="mb-4">
                        <label class="form-label fw-medium">
                            <i class="bi bi-send me-1 text-success"></i>Mesaj kanalı
                        </label>
                        <p class="text-muted small mb-2">
                            Randevu təsdiqi və xatırlatma mesajlarının hansı kanalla göndəriləcəyini seçin.
                        </p>

                        @if(!$whatsappAvailable)
                            <div class="alert alert-secondary small py-2 px-3 mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                WhatsApp hazırda aktiv deyil.
                                @if($canEdit)
                                    <a href="{{ route('panel.whatsapp.edit') }}" class="fw-semibold">Öz WhatsApp nömrənizi qoşun</a>
                                    və ya administrator ilə əlaqə saxlayın.
                                @else
                                    Müəssisə sahibi öz WhatsApp nömrəsini qoşa bilər.
                                @endif
                            </div>
                        @elseif($canEdit)
                            <div class="alert alert-light border small py-2 px-3 mb-3">
                                <i class="bi bi-whatsapp me-1 text-success"></i>
                                @if($whatsappOwn)
                                    Mesajlar öz WhatsApp nömrənizdən göndərilir.
                                @else
                                    Mesajlar platformanın ortaq WhatsApp nömrəsindən göndərilir.
                                @endif
                                <a href="{{ route('panel.whatsapp.edit') }}" class="fw-semibold">Bağlantı ayarları</a>
                            </div>
                        @endif

                        <div class="row g-2">
                            @foreach([
                                ['value' => 'sms',      'icon' => 'bi-chat-text',  'color' => 'primary', 'title' => 'Yalnız SMS',      'desc' => 'Adi qaydada SMS göndərilir.'],
                                ['value' => 'whatsapp', 'icon' => 'bi-whatsapp',   'color' => 'success', 'title' => 'Yalnız WhatsApp', 'desc' => 'Mesaj WhatsApp ilə göndərilir.'],
                                ['value' => 'both',     'icon' => 'bi-layers',     'color' => 'info',    'title' => 'SMS + WhatsApp',  'desc' => 'Hər iki kanala göndərilir.'],
                            ] as $opt)
                                @php
                                    $disabled = $opt['value'] !== 'sms' && !$whatsappAvailable;
                                @endphp
                                <div class="col-md-4">
                                    <input type="radio"
                                           class="btn-check"
                                           name="notify_channel"
                                           id="channel_{{ $opt['value'] }}"
                                           value="{{ $opt['value'] }}"
                                           {{ $currentChannel === $opt['value'] ? 'checked' : '' }}
                                           {{ $disabled ? 'disabled' : '' }}
                                           autocomplete="off">
                                    <label class="btn btn-outline-{{ $opt['color'] }} w-100 h-100 text-start p-3 {{ $disabled ? 'opacity-50' : '' }}"
                                           for="channel_{{ $opt['value'] }}">
                                        <i class="bi {{ $opt['icon'] }} fs-5 d-block mb-1"></i>
                                        <span class="fw-semibold d-block">{{ $opt['title'] }}</span>
                                        <span class="small d-block opacity-75">{{ $opt['desc'] }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        @error('notify_channel')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror

                        <div class="text-muted mt-2 small">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>SMS + WhatsApp</strong> seçildikdə hər randevu üçün iki mesaj göndərilir.
                            Paketinizdə mesaj limiti yoxdur.
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Appointment template --}}
                    <div class="mb-4">
                        <label class="form-label fw-medium">
                            <i class="bi bi-calendar-check me-1 text-primary"></i>Randevu Təsdiq mesajı
                        </label>
                        <p class="text-muted small mb-2">
                            Randevu yaradıldıqda müştəriyə göndərilir — seçdiyiniz hər kanal üçün eyni mətn işlədilir.
                        </p>
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            @foreach(['{ad_soyad}', '{xidmet}', '{mutexessis}', '{tarix}', '{saat}', '{muessise}', '{xerite}'] as $ph)
                                <button type="button" class="btn btn-outline-secondary btn-sm placeholder-btn"
                                        data-target="sms_appointment_template" data-placeholder="{{ $ph }}">{{ $ph }}</button>
                            @endforeach
                        </div>
                        <textarea id="sms_appointment_template"
                                  name="sms_appointment_template"
                                  class="form-control font-monospace @error('sms_appointment_template') is-invalid @enderror"
                                  rows="3" maxlength="160"
                                  placeholder="Boş buraxın — defolt şablon istifadə olunacaq">{{ old('sms_appointment_template', $clinic->sms_appointment_template ?? '') }}</textarea>
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
                            <i class="bi bi-bell me-1 text-warning"></i>Xatırlatma mesajı
                        </label>
                        <p class="text-muted small mb-2">
                            Randevudan əvvəl avtomatik göndərilir — seçdiyiniz hər kanal üçün eyni mətn işlədilir.
                        </p>
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            @foreach(['{ad_soyad}', '{xidmet}', '{mutexessis}', '{tarix}', '{saat}', '{muessise}'] as $ph)
                                <button type="button" class="btn btn-outline-secondary btn-sm placeholder-btn"
                                        data-target="sms_reminder_template" data-placeholder="{{ $ph }}">{{ $ph }}</button>
                            @endforeach
                        </div>
                        <textarea id="sms_reminder_template"
                                  name="sms_reminder_template"
                                  class="form-control font-monospace @error('sms_reminder_template') is-invalid @enderror"
                                  rows="3" maxlength="160"
                                  placeholder="Boş buraxın — defolt şablon istifadə olunacaq">{{ old('sms_reminder_template', $clinic->sms_reminder_template ?? '') }}</textarea>
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
                                       {{ ($clinic->sms_copy_to_self ?? false) ? 'checked' : '' }}
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

                    <button type="submit" class="btn btn-primary" @disabled(!$canEdit)>
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
                        <tr><td><code>{mutexessis}</code></td><td>Randevunu qəbul edən əməkdaş</td><td class="text-muted">Aysel Məmmədova</td></tr>
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
<script @cspNonce>
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
