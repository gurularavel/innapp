@extends('layouts.doctor')

@section('title', 'Profilim')
@section('page-title', 'Profilim')

@section('content')
<div class="row g-4 mb-4">
    {{-- Profile Info --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-circle me-2 text-primary"></i>Profil Məlumatları
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('panel.profile.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="name" class="form-label fw-medium">Ad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="surname" class="form-label fw-medium">Soyad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('surname') is-invalid @enderror"
                               id="surname" name="surname" value="{{ old('surname', auth()->user()->surname) }}" required>
                        @error('surname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-medium">Telefon</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                               id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                               placeholder="+994XX XXX XX XX">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="muessise_adi" class="form-label fw-medium">Müəssisə adı</label>
                        <input type="text" class="form-control @error('muessise_adi') is-invalid @enderror"
                               id="muessise_adi" name="muessise_adi"
                               value="{{ old('muessise_adi', auth()->user()->muessise_adi) }}"
                               maxlength="100"
                               placeholder="Şirkət, mərkəz, salon...">
                        @error('muessise_adi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">SMS şablonundakı <code>{muessise}</code> bu adla əvəzlənəcək.</div>
                    </div>

                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="text-muted small">Email</div>
                        <div class="fw-medium">{{ auth()->user()->email }}</div>
                        <div class="text-muted" style="font-size:.75rem;">Email dəyişdirilmir.</div>
                    </div>

                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="text-muted small">İxtisas</div>
                        <div class="fw-medium">{{ auth()->user()->specialty?->name ?? '—' }}</div>
                        <div class="text-muted" style="font-size:.75rem;">İxtisası administrator dəyişir.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Məlumatları Yenilə
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Password Change --}}
    <div class="col-lg-6" id="password-card">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-shield-lock me-2 text-warning"></i>Şifrə Dəyişdir
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('panel.profile.password') }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-medium">Cari Şifrə <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                               id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium">Yeni Şifrə <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-medium">Yeni Şifrə Təkrarı <span class="text-danger">*</span></label>
                        <input type="password" class="form-control"
                               id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Şifrəniz ən azı 8 simvoldan ibarət olmalıdır.
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-shield-check me-1"></i>Şifrəni Dəyiş
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- SMS Şablonları --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-chat-dots me-2 text-info"></i>SMS Şablonları
        </h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Boş buraxsanız sistem tərəfindən müəyyən edilmiş defolt şablon istifadə olunacaq.
        </p>
        <form method="POST" action="{{ route('panel.profile.sms-templates.save') }}">
            @csrf
            @method('PUT')

            {{-- Appointment template --}}
            <div class="mb-4">
                <label class="form-label fw-medium">
                    <i class="bi bi-calendar-check me-1 text-primary"></i>Randevu Təsdiq SMS
                </label>
                <div class="mb-2 d-flex flex-wrap gap-1">
                    @foreach(['{ad_soyad}', '{xidmet}', '{tarix}', '{saat}', '{muessise}'] as $ph)
                        <button type="button" class="btn btn-outline-secondary btn-sm placeholder-btn"
                                data-target="sms_appointment_template" data-placeholder="{{ $ph }}">{{ $ph }}</button>
                    @endforeach
                </div>
                <textarea id="sms_appointment_template"
                          name="sms_appointment_template"
                          class="form-control font-monospace @error('sms_appointment_template') is-invalid @enderror"
                          rows="3"
                          maxlength="160"
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
                <div class="mb-2 d-flex flex-wrap gap-1">
                    @foreach(['{ad_soyad}', '{xidmet}', '{tarix}', '{saat}', '{muessise}'] as $ph)
                        <button type="button" class="btn btn-outline-secondary btn-sm placeholder-btn"
                                data-target="sms_reminder_template" data-placeholder="{{ $ph }}">{{ $ph }}</button>
                    @endforeach
                </div>
                <textarea id="sms_reminder_template"
                          name="sms_reminder_template"
                          class="form-control font-monospace @error('sms_reminder_template') is-invalid @enderror"
                          rows="3"
                          maxlength="160"
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

            {{-- Placeholder docs --}}
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Yer tutucu</th>
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
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-info text-white">
                <i class="bi bi-check-lg me-1"></i>SMS Şablonlarını Yadda Saxla
            </button>
        </form>
    </div>
</div>

{{-- İş Saatları Kartı --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-clock me-2 text-success"></i>İş Saatları
        </h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('panel.profile.working-hours.save') }}">
            @csrf
            @method('PUT')

            {{-- İş saatları cədvəli --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:160px">Gün</th>
                            <th style="width:80px" class="text-center">İş günü</th>
                            <th>Başlanğıc</th>
                            <th>Son</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $dayNames = [
                                1 => 'Bazar ertəsi',
                                2 => 'Çərşənbə axşamı',
                                3 => 'Çərşənbə',
                                4 => 'Cümə axşamı',
                                5 => 'Cümə',
                                6 => 'Şənbə',
                                7 => 'Bazar',
                            ];
                        @endphp
                        @for($d = 1; $d <= 7; $d++)
                        @php $wh = $workingHours[$d] ?? null; @endphp
                        <tr>
                            <td class="fw-medium">{{ $dayNames[$d] }}</td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input class="form-check-input wh-checkbox" type="checkbox"
                                           name="working_hours[{{ $d }}][is_working]" value="1"
                                           data-day="{{ $d }}"
                                           {{ ($wh && $wh->is_working) ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm wh-time"
                                       name="working_hours[{{ $d }}][start_time]"
                                       data-day="{{ $d }}"
                                       value="{{ $wh ? substr($wh->start_time, 0, 5) : '09:00' }}"
                                       placeholder="09:00" maxlength="5" pattern="[0-2][0-9]:[0-5][0-9]"
                                       {{ ($wh && !$wh->is_working) ? 'disabled' : '' }}>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm wh-time"
                                       name="working_hours[{{ $d }}][end_time]"
                                       data-day="{{ $d }}"
                                       value="{{ $wh ? substr($wh->end_time, 0, 5) : '18:00' }}"
                                       placeholder="18:00" maxlength="5" pattern="[0-2][0-9]:[0-5][0-9]"
                                       {{ ($wh && !$wh->is_working) ? 'disabled' : '' }}>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- Fasilələr --}}
            <h6 class="fw-semibold mb-3">
                <i class="bi bi-cup-hot me-1 text-warning"></i>Fasilələr
            </h6>
            <div id="breaks-container">
                @if(isset($existingBreaks) && $existingBreaks->count())
                    @foreach($existingBreaks as $bi => $brk)
                    <div class="row g-2 align-items-center mb-2 break-row">
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" name="breaks[{{ $bi }}][day_of_week]">
                                @for($d = 1; $d <= 7; $d++)
                                    <option value="{{ $d }}" {{ $brk->day_of_week == $d ? 'selected' : '' }}>{{ $dayNames[$d] }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm" name="breaks[{{ $bi }}][start_time]" value="{{ substr($brk->start_time, 0, 5) }}" placeholder="13:00" maxlength="5" pattern="[0-2][0-9]:[0-5][0-9]">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm" name="breaks[{{ $bi }}][end_time]" value="{{ substr($brk->end_time, 0, 5) }}" placeholder="14:00" maxlength="5" pattern="[0-2][0-9]:[0-5][0-9]">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" name="breaks[{{ $bi }}][label]" value="{{ $brk->label }}" placeholder="Nahar, Şəxsi...">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-break">
                                <i class="bi bi-trash"></i> Sil
                            </button>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>

            <button type="button" id="add-break" class="btn btn-outline-secondary btn-sm mb-4">
                <i class="bi bi-plus-circle me-1"></i>Fasilə əlavə et
            </button>

            <div class="d-flex">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-lg me-1"></i>İş Saatlarını Yadda Saxla
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// SMS template char counters + placeholder insert
(function () {
    function updateCount(textarea, countEl) {
        countEl.textContent = textarea.value.length;
        countEl.classList.toggle('text-danger', textarea.value.length > 140);
    }
    const apptArea  = document.getElementById('sms_appointment_template');
    const remArea   = document.getElementById('sms_reminder_template');
    const apptCount = document.getElementById('appt-count');
    const remCount  = document.getElementById('rem-count');

    if (apptArea) {
        updateCount(apptArea, apptCount);
        apptArea.addEventListener('input', () => updateCount(apptArea, apptCount));
    }
    if (remArea) {
        updateCount(remArea, remCount);
        remArea.addEventListener('input', () => updateCount(remArea, remCount));
    }

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

// Working hours / breaks logic
(function () {
    // 24h time mask: "0900" → "09:00", onBlur format
    function applyTimeMask(inp) {
        inp.addEventListener('input', function () {
            let v = this.value.replace(/[^0-9]/g, '');
            if (v.length >= 3) {
                v = v.slice(0, 2) + ':' + v.slice(2, 4);
            }
            this.value = v;
        });
        inp.addEventListener('blur', function () {
            const m = this.value.match(/^(\d{1,2}):?(\d{0,2})$/);
            if (m) {
                const hh = m[1].padStart(2, '0');
                const mm = (m[2] || '00').padEnd(2, '0');
                this.value = hh + ':' + mm;
            }
        });
    }
    document.querySelectorAll('input[pattern="[0-2][0-9]:[0-5][0-9]"]').forEach(applyTimeMask);

    // Apply mask to dynamically added rows too
    const breaksContainer = document.getElementById('breaks-container');
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
            m.addedNodes.forEach(function (node) {
                if (node.querySelectorAll) {
                    node.querySelectorAll('input[pattern]').forEach(applyTimeMask);
                }
            });
        });
    });
    if (breaksContainer) observer.observe(breaksContainer, { childList: true });

    // Toggle time inputs when is_working checkbox changes
    document.querySelectorAll('.wh-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const day = this.dataset.day;
            document.querySelectorAll('.wh-time[data-day="' + day + '"]').forEach(function (inp) {
                inp.disabled = !cb.checked;
            });
        });
    });

    // Add break row
    let breakIndex = {{ isset($existingBreaks) ? $existingBreaks->count() : 0 }};
    const dayOptions = [
        {value: 1, label: "Bazar ertəsi"},
        {value: 2, label: "Çərşənbə axşamı"},
        {value: 3, label: "Çərşənbə"},
        {value: 4, label: "Cümə axşamı"},
        {value: 5, label: "Cümə"},
        {value: 6, label: "Şənbə"},
        {value: 7, label: "Bazar"}
    ];

    document.getElementById('add-break').addEventListener('click', function () {
        const container = document.getElementById('breaks-container');
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-center mb-2 break-row';

        let opts = dayOptions.map(o => `<option value="${o.value}">${o.label}</option>`).join('');

        row.innerHTML = `
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="breaks[${breakIndex}][day_of_week]">${opts}</select>
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm" name="breaks[${breakIndex}][start_time]" value="13:00" placeholder="13:00" maxlength="5" pattern="[0-2][0-9]:[0-5][0-9]">
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm" name="breaks[${breakIndex}][end_time]" value="14:00" placeholder="14:00" maxlength="5" pattern="[0-2][0-9]:[0-5][0-9]">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="breaks[${breakIndex}][label]" placeholder="Nahar, Şəxsi...">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger remove-break">
                    <i class="bi bi-trash"></i> Sil
                </button>
            </div>`;

        container.appendChild(row);
        breakIndex++;
    });

    // Remove break row (event delegation)
    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-break')) {
            e.target.closest('.break-row').remove();
        }
    });
})();
</script>
@endpush
