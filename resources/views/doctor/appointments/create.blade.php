@extends('layouts.doctor')

@section('title', 'Yeni Randevu')
@section('page-title', 'Yeni Randevu')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Yeni Randevu Əlavə Et</h6>
                <a href="{{ route('panel.appointments.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('panel.appointments.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Müştəri <span class="text-danger">*</span></label>
                            <input type="hidden" name="patient_id" id="patient_id"
                                   value="{{ old('patient_id', request('patient_id')) }}">
                            <div class="position-relative">
                                <input type="text" id="patient_search"
                                       class="form-control @error('patient_id') is-invalid @enderror"
                                       placeholder="Ad, soyad və ya telefon yazın..."
                                       autocomplete="off"
                                       value="{{ old('patient_id', request('patient_id')) ? ($patients->find(old('patient_id', request('patient_id')))?->full_name ?? '') : '' }}">
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
                                            {{ old('treatment_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                        @if($type->duration_minutes) ({{ $type->duration_minutes }} dəq) @endif
                                    </option>
                                @endforeach
                                <option value="__add_new__" style="color:#0d6efd;font-weight:500;">+ Yeni növ əlavə et</option>
                            </select>
                            @error('treatment_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="scheduled_date" class="form-label fw-medium">Tarix və Saat <span class="text-danger">*</span></label>
                            @php
                                $oldScheduled = old('scheduled_at');
                                $oldTs = $oldScheduled ? strtotime($oldScheduled) : null;
                                $oldDate = $oldTs ? date('Y-m-d', $oldTs) : '';
                                $oldTime = $oldTs ? date('H:i', $oldTs) : '';
                            @endphp
                            <input type="hidden" id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at') }}">
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
                                   value="{{ old('duration_minutes', 30) }}">
                            @error('duration_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-medium">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status">
                                <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Gözləyir</option>
                                <option value="confirmed" {{ old('status') === 'confirmed' ? 'selected' : '' }}>Təsdiqləndi</option>
                                <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                                <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Ləğv edildi</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label fw-medium">Qeydlər</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="Randevu haqqında əlavə qeydlər...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                        </button>
                        <a href="{{ route('panel.appointments.index') }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Yeni xidmət növü --}}
<div class="modal fade" id="addTreatmentTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold"><i class="bi bi-plus-circle me-1 text-primary"></i>Yeni Xidmət Növü</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-error" class="alert alert-danger d-none"></div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Ad <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="modal-name" placeholder="Xidmət adı">
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-medium">Qiymət (₼)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="modal-price" placeholder="İxtiyari">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-medium">Müddət (dəq)</label>
                        <input type="number" min="5" step="5" class="form-control" id="modal-duration" value="30">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-medium">Rəng</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" class="form-control form-control-color" id="modal-color" value="#3788d8" style="width:50px;height:38px;">
                        <span class="text-muted small">Təqvimdə göstəriləcək rəng</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Ləğv et</button>
                <button type="button" class="btn btn-primary" id="modal-save-btn">
                    <span id="modal-save-text"><i class="bi bi-check-lg me-1"></i>Əlavə et</span>
                    <span id="modal-save-spinner" class="d-none"><span class="spinner-border spinner-border-sm me-1"></span>Gözləyin...</span>
                </button>
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
const slotsUrl         = '{{ route('panel.appointments.available-slots') }}';
const scheduledAtInput = document.getElementById('scheduled_at');
const scheduledDateInput = document.getElementById('scheduled_date');
const slotsContainer   = document.getElementById('slots-container');
const slotsList        = document.getElementById('slots-list');
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
        fetch(`${slotsUrl}?date=${date}&duration=${duration}`)
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

// ── Tom Select: Xidmət növü ───────────────────────────────────────────────────
const treatmentTS = new TomSelect('#treatment_type_id', {
    create: false,
    allowEmptyOption: true,
    onDropdownOpen: function() { const s = this; setTimeout(() => s.setTextboxValue(''), 0); },
    render: {
        option: function(data, escape) {
            if (data.value === '__add_new__')
                return `<div class="option" style="color:#0d6efd;font-weight:500;">${escape(data.text)}</div>`;
            return `<div class="option">${escape(data.text)}</div>`;
        }
    },
    onChange: function(value) {
        if (value === '__add_new__') {
            this.setValue(this._prev ?? '', true);
            new bootstrap.Modal(document.getElementById('addTreatmentTypeModal')).show();
            return;
        }
        this._prev = value;
        const duration = this.options[value]?.duration;
        if (duration) {
            document.getElementById('duration_minutes').value = duration;
            clearSelection();
            fetchSlots();
        }
    }
});
treatmentTS._prev = treatmentTS.getValue();

// ── Modal: add new treatment type ─────────────────────────────────────────────
document.getElementById('modal-save-btn').addEventListener('click', function () {
    const name     = document.getElementById('modal-name').value.trim();
    const price    = document.getElementById('modal-price').value;
    const duration = document.getElementById('modal-duration').value;
    const color    = document.getElementById('modal-color').value;
    const errorBox = document.getElementById('modal-error');
    errorBox.classList.add('d-none');

    if (!name) {
        errorBox.textContent = 'Ad mütləq doldurulmalıdır.';
        errorBox.classList.remove('d-none');
        document.getElementById('modal-name').focus();
        return;
    }

    const saveText    = document.getElementById('modal-save-text');
    const saveSpinner = document.getElementById('modal-save-spinner');
    saveText.classList.add('d-none');
    saveSpinner.classList.remove('d-none');
    this.disabled = true;

    fetch('{{ route('panel.treatment-types.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        },
        body: JSON.stringify({ name, price: price || null, duration_minutes: duration || 30, color: color || '#3788d8' }),
    })
    .then(async r => { const d = await r.json(); if (!r.ok) throw new Error(d.errors?.name?.[0] || d.message || 'Xəta'); return d; })
    .then(type => {
        treatmentTS.addOption({ value: String(type.id), text: type.name + (type.duration_minutes ? ` (${type.duration_minutes} dəq)` : ''), duration: type.duration_minutes ?? 30 });
        treatmentTS.setValue(String(type.id));
        document.getElementById('modal-name').value = '';
        document.getElementById('modal-price').value = '';
        document.getElementById('modal-duration').value = '30';
        document.getElementById('modal-color').value = '#3788d8';
        bootstrap.Modal.getInstance(document.getElementById('addTreatmentTypeModal')).hide();
    })
    .catch(err => { errorBox.textContent = err.message; errorBox.classList.remove('d-none'); })
    .finally(() => { saveText.classList.remove('d-none'); saveSpinner.classList.add('d-none'); document.getElementById('modal-save-btn').disabled = false; });
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
