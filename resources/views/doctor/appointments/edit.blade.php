@extends('layouts.doctor')

@section('title', 'Randevunu Düzəlt')
@section('page-title', 'Randevunu Düzəlt')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Randevunu Düzəlt</h6>
                <a href="{{ route('doctor.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}">
                    @csrf
                    @method('PATCH')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Müştəri <span class="text-danger">*</span></label>
                            <input type="hidden" name="patient_id" id="patient_id"
                                   value="{{ old('patient_id', $appointment->patient_id) }}">
                            <div class="position-relative">
                                <input type="text" id="patient_search"
                                       class="form-control @error('patient_id') is-invalid @enderror"
                                       placeholder="Ad, soyad və ya telefon yazın..."
                                       autocomplete="off"
                                       value="{{ old('patient_id') ? '' : $appointment->patient->full_name }}">
                                <div id="patient_dropdown"
                                     class="list-group position-absolute w-100 shadow"
                                     style="z-index:1000;display:none;max-height:220px;overflow-y:auto;"></div>
                            </div>
                            @error('patient_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div id="patient_info" class="alert alert-info small mt-1 py-2" style="display:none"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="treatment_type_id" class="form-label fw-medium">Xidmət Növü</label>
                            <select class="form-select @error('treatment_type_id') is-invalid @enderror"
                                    id="treatment_type_id" name="treatment_type_id">
                                <option value="">— Seçin (ixtiyari) —</option>
                                @foreach($treatmentTypes as $type)
                                    <option value="{{ $type->id }}"
                                            data-duration="{{ $type->duration_minutes ?? 30 }}"
                                            {{ old('treatment_type_id', $appointment->treatment_type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                        @if($type->duration_minutes) ({{ $type->duration_minutes }} dəq) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('treatment_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="scheduled_at" class="form-label fw-medium">Tarix və Saat <span class="text-danger">*</span></label>
                            @php
                                $oldScheduled = old('scheduled_at', $appointment->scheduled_at->format('Y-m-d H:i'));
                                $oldTs = $oldScheduled ? strtotime($oldScheduled) : null;
                                $oldDate = $oldTs ? date('Y-m-d', $oldTs) : '';
                                $oldTime = $oldTs ? date('H:i', $oldTs) : '';
                            @endphp
                            <input type="hidden" id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at', $appointment->scheduled_at->format('Y-m-d\TH:i')) }}">
                            <div class="row g-2">
                                <div class="col-7">
                                    <input type="date" class="form-control @error('scheduled_at') is-invalid @enderror"
                                           id="scheduled_date" value="{{ $oldDate }}" required>
                                </div>
                                <div class="col-5">
                                    <input type="text" class="form-control @error('scheduled_at') is-invalid @enderror"
                                           id="scheduled_time" value="{{ $oldTime }}"
                                           placeholder="14:30" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" required>
                                </div>
                            </div>
                            <div class="form-text">24-saat formatı istifadə edin (məs: 09:00, 14:30).</div>
                            @error('scheduled_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="conflict-warning" class="alert alert-danger mt-2 py-2 small" style="display:none">
                                <i class="bi bi-exclamation-triangle me-1"></i>Bu vaxtda toqquşma var:
                                <ul id="conflict-list" class="mb-0 mt-1"></ul>
                            </div>
                            <div id="slots-container" class="mt-2" style="display:none">
                                <div class="text-muted small mb-1">Boş vaxtlar:</div>
                                <div id="slots-list" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="duration_minutes" class="form-label fw-medium">Müddət (dəqiqə)</label>
                            <input type="number" min="5" step="5"
                                   class="form-control @error('duration_minutes') is-invalid @enderror"
                                   id="duration_minutes" name="duration_minutes"
                                   value="{{ old('duration_minutes', $appointment->duration_minutes) }}">
                            @error('duration_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-medium">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status">
                                <option value="pending" {{ old('status', $appointment->status) === 'pending' ? 'selected' : '' }}>Gözləyir</option>
                                <option value="confirmed" {{ old('status', $appointment->status) === 'confirmed' ? 'selected' : '' }}>Təsdiqləndi</option>
                                <option value="completed" {{ old('status', $appointment->status) === 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                                <option value="cancelled" {{ old('status', $appointment->status) === 'cancelled' ? 'selected' : '' }}>Ləğv edildi</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label fw-medium">Qeydlər</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3">{{ old('notes', $appointment->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                        </button>
                        <a href="{{ route('doctor.appointments.show', $appointment) }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Müştəri Autocomplete ─────────────────────────────────────────────────────
(function () {
    const searchInput  = document.getElementById('patient_search');
    const hiddenInput  = document.getElementById('patient_id');
    const dropdown     = document.getElementById('patient_dropdown');
    const infoBox      = document.getElementById('patient_info');
    const searchUrl    = '{{ route('doctor.patients.search') }}';
    let acTimer = null;

    searchInput.addEventListener('keyup', function () {
        clearTimeout(acTimer);
        const q = this.value.trim();
        if (q.length < 2) { dropdown.style.display = 'none'; return; }

        acTimer = setTimeout(function () {
            fetch(`${searchUrl}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(patients => {
                    if (!patients.length) { dropdown.style.display = 'none'; return; }
                    dropdown.innerHTML = patients.map(p => {
                        const bd   = p.birth_date ? p.birth_date.split('T')[0] : null;
                        const age  = bd ? (new Date().getFullYear() - new Date(bd).getFullYear()) : null;
                        const info = [p.phone, age ? age + ' yaş' : null].filter(Boolean).join(' | ');
                        return `<button type="button" class="list-group-item list-group-item-action py-2 px-3 patient-item"
                                    data-id="${p.id}" data-name="${p.name} ${p.surname}"
                                    data-phone="${p.phone ?? ''}" data-birth="${bd ?? ''}">
                                    <span class="fw-medium">${p.name} ${p.surname}</span>
                                    <small class="text-muted ms-2">${info}</small>
                                </button>`;
                    }).join('');
                    dropdown.style.display = 'block';
                })
                .catch(() => {});
        }, 300);
    });

    document.addEventListener('click', function (e) {
        const item = e.target.closest('.patient-item');
        if (item) {
            hiddenInput.value      = item.dataset.id;
            searchInput.value      = item.dataset.name;
            dropdown.style.display = 'none';

            const phone = item.dataset.phone;
            const birth = item.dataset.birth;
            let parts = [];
            if (phone) parts.push('Tel: ' + phone);
            if (birth) {
                const age = new Date().getFullYear() - new Date(birth).getFullYear();
                parts.push('Doğum: ' + birth + ' (' + age + ' yaş)');
            }
            if (parts.length) {
                infoBox.textContent   = parts.join(' · ');
                infoBox.style.display = 'block';
            }
            return;
        }
        if (!e.target.closest('#patient_search') && !e.target.closest('#patient_dropdown')) {
            dropdown.style.display = 'none';
        }
    });

    searchInput.addEventListener('input', function () {
        if (!this.value.trim()) {
            hiddenInput.value = '';
            infoBox.style.display = 'none';
        }
    });
})();

    document.getElementById('treatment_type_id').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const duration = selected.getAttribute('data-duration');
        if (duration) {
            document.getElementById('duration_minutes').value = duration;
            fetchSlots();
        }
    });

    // Available slots & conflict check
    let slotTimer = null;
    const slotsUrl    = '{{ route('doctor.appointments.available-slots') }}';
    const excludeId   = '{{ $appointment->id }}';
    const scheduledAtInput = document.getElementById('scheduled_at');
    const scheduledDateInput = document.getElementById('scheduled_date');
    const scheduledTimeInput = document.getElementById('scheduled_time');

    function normalizeTimeInput(raw) {
        const input = String(raw || '').trim();
        const digitsOnly = input.replace(/\D/g, '');

        if (!input.includes(':')) {
            if (digitsOnly.length === 4) return digitsOnly.slice(0, 2) + ':' + digitsOnly.slice(2);
            if (digitsOnly.length === 3) return '0' + digitsOnly.slice(0, 1) + ':' + digitsOnly.slice(1);
            return input.slice(0, 5);
        }

        const parts = input.split(':');
        const h = (parts[0] || '').replace(/\D/g, '');
        const m = (parts[1] || '').replace(/\D/g, '').slice(0, 2);

        if (!h.length) return m.length ? ':' + m : '';
        const hh = h.length === 1 ? ('0' + h) : h.slice(0, 2);
        return m.length ? (hh + ':' + m) : hh;
    }

    function updateScheduledAtFromParts() {
        const date = scheduledDateInput.value;
        const time = normalizeTimeInput(scheduledTimeInput.value);
        scheduledTimeInput.value = time;
        if (/^([01]\d|2[0-3]):[0-5]\d$/.test(time) && date) {
            scheduledAtInput.value = `${date}T${time}`;
        } else {
            scheduledAtInput.value = '';
        }
    }

    function fetchSlots() {
        updateScheduledAtFromParts();
        const scheduledAt = scheduledAtInput.value;
        const duration    = document.getElementById('duration_minutes').value;
        if (!scheduledAt || !duration) return;

        const date = scheduledAt.split('T')[0];

        clearTimeout(slotTimer);
        slotTimer = setTimeout(function () {
            fetch(`${slotsUrl}?date=${date}&duration=${duration}&exclude_id=${excludeId}&scheduled_at=${encodeURIComponent(scheduledAt)}`)
                .then(r => r.json())
                .then(data => {
                    const warning   = document.getElementById('conflict-warning');
                    const list      = document.getElementById('conflict-list');
                    const container = document.getElementById('slots-container');
                    const slotsList = document.getElementById('slots-list');

                    if (data.conflicts && data.conflicts.length) {
                        list.innerHTML = data.conflicts.map(c =>
                            `<li>${c.patient_name} — ${c.start}–${c.end}</li>`
                        ).join('');
                        warning.style.display = 'block';
                    } else {
                        warning.style.display = 'none';
                        list.innerHTML = '';
                    }

                    if (data.available_slots && data.available_slots.length) {
                        slotsList.innerHTML = data.available_slots.map(s =>
                            `<button type="button" class="btn btn-sm btn-outline-primary slot-btn" data-time="${s}" data-date="${date}">${s}</button>`
                        ).join('');
                        container.style.display = 'block';
                    } else {
                        container.style.display = 'none';
                        slotsList.innerHTML = '';
                    }
                })
                .catch(() => {});
        }, 400);
    }

    scheduledDateInput.addEventListener('change', fetchSlots);
    scheduledTimeInput.addEventListener('input', function () {
        this.value = normalizeTimeInput(this.value);
        fetchSlots();
    });
    scheduledTimeInput.addEventListener('blur', function () {
        this.value = normalizeTimeInput(this.value);
        fetchSlots();
    });
    document.getElementById('duration_minutes').addEventListener('input', fetchSlots);

    document.addEventListener('click', function (e) {
        if (e.target.closest('.slot-btn')) {
            const btn  = e.target.closest('.slot-btn');
            const date = btn.dataset.date;
            const time = btn.dataset.time;
            scheduledDateInput.value = date;
            scheduledTimeInput.value = time;
            updateScheduledAtFromParts();
            fetchSlots();
        }
    });

    updateScheduledAtFromParts();
    if (scheduledAtInput.value) {
        fetchSlots();
    }
</script>
@endpush
