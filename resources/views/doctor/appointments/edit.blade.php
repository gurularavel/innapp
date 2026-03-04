@extends('layouts.doctor')

@section('title', 'Randevunu Düzəlt')
@section('page-title', 'Randevunu Düzəlt')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Randevunu Düzəlt</h6>
                <a href="{{ route('panel.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('panel.appointments.update', $appointment) }}">
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
                            <label for="scheduled_date" class="form-label fw-medium">Tarix və Saat <span class="text-danger">*</span></label>
                            @php
                                $oldScheduled = old('scheduled_at', $appointment->scheduled_at->format('Y-m-d H:i'));
                                $oldTs = $oldScheduled ? strtotime($oldScheduled) : null;
                                $oldDate = $oldTs ? date('Y-m-d', $oldTs) : '';
                                $oldTime = $oldTs ? date('H:i', $oldTs) : '';
                            @endphp
                            <input type="hidden" id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at', $appointment->scheduled_at->format('Y-m-d\TH:i')) }}">
                            <input type="date" class="form-control @error('scheduled_at') is-invalid @enderror"
                                   id="scheduled_date" value="{{ $oldDate }}" required>
                            @error('scheduled_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                        <a href="{{ route('panel.appointments.show', $appointment) }}" class="btn btn-outline-secondary">Ləğv et</a>
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
    const searchUrl    = '{{ route('panel.patients.search') }}';
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

// ── Slot Picker ───────────────────────────────────────────────────────────────
const slotsUrl           = '{{ route('panel.appointments.available-slots') }}';
const excludeId          = '{{ $appointment->id }}';
const scheduledAtInput   = document.getElementById('scheduled_at');
const scheduledDateInput = document.getElementById('scheduled_date');
const slotsContainer     = document.getElementById('slots-container');
const slotsList          = document.getElementById('slots-list');
let selectedTime = '{{ $oldTime }}';
let slotTimer = null;

function clearSelection() {
    selectedTime = '';
    scheduledAtInput.value = '';
}

function renderSlots(slots, date) {
    if (!slots.length) {
        slotsList.innerHTML = '<span class="text-muted small">Bu gün boş vaxt yoxdur</span>';
        slotsContainer.style.display = 'block';
        return;
    }
    slotsList.innerHTML = slots.map(s => {
        const active = s === selectedTime;
        return `<button type="button" class="btn btn-sm ${active ? 'btn-primary' : 'btn-outline-primary'} slot-btn" data-time="${s}" data-date="${date}">${s}</button>`;
    }).join('');
    slotsContainer.style.display = 'block';
}

function fetchSlots() {
    const date     = scheduledDateInput.value;
    const duration = document.getElementById('duration_minutes').value;
    if (!date) { slotsContainer.style.display = 'none'; return; }
    clearTimeout(slotTimer);
    slotTimer = setTimeout(function () {
        fetch(`${slotsUrl}?date=${date}&duration=${duration}&exclude_id=${excludeId}`)
            .then(r => r.json())
            .then(data => {
                if (data.working === false) {
                    slotsList.innerHTML = '<span class="text-muted small"><i class="bi bi-calendar-x me-1"></i>Bu gün iş günü deyil</span>';
                    slotsContainer.style.display = 'block';
                    return;
                }
                renderSlots(data.available_slots || [], date);
            })
            .catch(() => {});
    }, 400);
}

document.getElementById('treatment_type_id').addEventListener('change', function () {
    const duration = this.options[this.selectedIndex].getAttribute('data-duration');
    if (duration) {
        document.getElementById('duration_minutes').value = duration;
        clearSelection();
        fetchSlots();
    }
});

scheduledDateInput.addEventListener('change', function () {
    clearSelection();
    fetchSlots();
});

document.getElementById('duration_minutes').addEventListener('input', function () {
    clearSelection();
    fetchSlots();
});

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.slot-btn');
    if (!btn) return;
    selectedTime = btn.dataset.time;
    scheduledAtInput.value = `${btn.dataset.date}T${selectedTime}`;
    document.querySelectorAll('.slot-btn').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline-primary');
    });
    btn.classList.remove('btn-outline-primary');
    btn.classList.add('btn-primary');
});

if (scheduledDateInput.value) {
    fetchSlots();
}
</script>
@endpush
