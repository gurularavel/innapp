@extends('layouts.doctor')

@section('title', 'Təqvim')
@section('page-title', 'Təqvim')

@push('styles')
<style>
    #calendar {
        background: #fff;
        padding: 1rem;
        border-radius: .5rem;
    }
    .fc-toolbar-title { font-size: 1.1rem !important; font-weight: 600; }
    .fc-event { cursor: pointer; border: none !important; }
    .fc-event-title { font-weight: 500; padding: 0 2px; }

    /* "+" button on day headers */
    .fc-add-btn {
        background: none;
        border: none;
        padding: 0 0 0 4px;
        color: rgba(255,255,255,.7);
        font-size: .85rem;
        cursor: pointer;
        line-height: 1;
        vertical-align: middle;
        transition: color .15s;
    }
    .fc-add-btn:hover { color: #fff; }
    .fc-daygrid-day-top .fc-add-btn {
        color: #6c757d;
        font-size: .8rem;
    }
    .fc-daygrid-day-top .fc-add-btn:hover { color: #0f4c75; }

    /* FAB for mobile */
    .calendar-fab {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 1040;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(0,0,0,.25);
        font-size: 1.4rem;
        display: none;
    }

    /* ── Mobile responsive ── */
    @media (max-width: 767.98px) {
        #calendar { padding: .5rem; }
        .fc-toolbar-title { font-size: .9rem !important; }
        .fc .fc-toolbar { flex-wrap: wrap; gap: .35rem; }
        .fc .fc-toolbar-chunk { display: flex; flex-wrap: wrap; gap: .25rem; }
        .fc .fc-button { font-size: .75rem !important; padding: .2rem .45rem !important; }
        .fc .fc-button-group .fc-button { padding: .2rem .4rem !important; }
        .fc-list-event td { padding: .35rem .5rem !important; font-size: .82rem; }
        .fc-list-day-cushion { font-size: .82rem !important; padding: .3rem .5rem !important; }
        .fc-timegrid-slot-label { font-size: .72rem !important; }
        .fc-col-header-cell-cushion { font-size: .78rem !important; }
        .fc-event-title { font-size: .75rem; }
        .fc-event-time  { font-size: .72rem; }
        .calendar-fab   { display: flex; align-items: center; justify-content: center; }
    }

    /* Patient autocomplete in modal */
    #qa-patient-dropdown {
        position: absolute;
        top: 100%; left: 0; right: 0;
        z-index: 1060;
        background: #fff;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 .375rem .375rem;
        box-shadow: 0 4px 12px rgba(0,0,0,.1);
        max-height: 200px;
        overflow-y: auto;
    }
    .qa-patient-item { padding: 8px 12px; cursor: pointer; font-size: .875rem; border-bottom: 1px solid #f0f0f0; }
    .qa-patient-item:last-child { border-bottom: none; }
    .qa-patient-item:hover { background: #f0f6ff; }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-sm-3 p-0">
        <div id="calendar"></div>
    </div>
</div>

{{-- FAB for mobile --}}
<button class="calendar-fab btn btn-primary d-md-none" id="fab-add" title="Randevu əlavə et">
    <i class="bi bi-plus-lg"></i>
</button>

{{-- Quick Add Modal --}}
<div class="modal fade" id="quickAddModal" tabindex="-1" aria-labelledby="quickAddLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-semibold" id="quickAddLabel">
                    <i class="bi bi-calendar-plus me-2 text-primary"></i>Yeni Randevu
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Error alert --}}
                <div id="qa-error" class="alert alert-danger py-2 small d-none"></div>

                <form id="qa-form">
                    @csrf
                    <input type="hidden" name="_ajax" value="1">
                    <input type="hidden" name="patient_id"    id="qa-patient-id">
                    <input type="hidden" name="scheduled_at"  id="qa-scheduled-at">
                    <input type="hidden" name="status"        value="pending">

                    {{-- Patient search --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Müştəri <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" id="qa-patient-search" class="form-control"
                                   placeholder="Ad, soyad və ya telefon..." autocomplete="off">
                            <div id="qa-patient-dropdown" class="d-none"></div>
                        </div>
                        <div id="qa-patient-info" class="alert alert-info small mt-1 py-1 d-none"></div>
                    </div>

                    {{-- Treatment type --}}
                    <div class="mb-3">
                        <label for="qa-treatment" class="form-label fw-medium">Xidmət Növü</label>
                        <select class="form-select" id="qa-treatment" name="treatment_type_id">
                            <option value="">— Seçin (ixtiyari) —</option>
                            @foreach($treatmentTypes as $tt)
                                <option value="{{ $tt->id }}" data-duration="{{ $tt->duration_minutes ?? 30 }}">
                                    {{ $tt->name }}
                                    @if($tt->duration_minutes) ({{ $tt->duration_minutes }} dəq) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date + Time --}}
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label for="qa-date" class="form-label fw-medium">Tarix <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="qa-date" required>
                        </div>
                        <div class="col-5">
                            <label for="qa-time" class="form-label fw-medium">Saat <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="qa-time" required>
                        </div>
                    </div>

                    {{-- Duration --}}
                    <div class="mb-3">
                        <label for="qa-duration" class="form-label fw-medium">Müddət (dəq)</label>
                        <input type="number" class="form-control" id="qa-duration" name="duration_minutes"
                               value="30" min="5" step="5">
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Ləğv et</button>
                <button type="button" class="btn btn-primary" id="qa-save">
                    <span id="qa-save-text"><i class="bi bi-check-lg me-1"></i>Yadda Saxla</span>
                    <span id="qa-save-spinner" class="d-none">
                        <span class="spinner-border spinner-border-sm me-1"></span>Gözləyin...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Event Detail Modal --}}
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-semibold" id="eventModalLabel">
                    <i class="bi bi-calendar-check me-2 text-primary"></i>Randevu Detalı
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="text-muted small">Müştəri</div>
                    <div class="fw-semibold fs-5" id="modal-patient">—</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Xidmət Növü</div>
                    <div class="fw-medium" id="modal-treatment">—</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Tarix / Saat</div>
                    <div class="fw-medium" id="modal-datetime">—</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Müddət</div>
                    <div class="fw-medium" id="modal-duration">—</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Status</div>
                    <div id="modal-status">—</div>
                </div>
                <div id="modal-notes-block" class="mb-3" style="display:none;">
                    <div class="text-muted small">Qeydlər</div>
                    <div class="fw-medium small mt-1 p-2 bg-light rounded" id="modal-notes">—</div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <a href="#" id="modal-link" class="btn btn-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Randevuya Keç
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Bağla</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Modals ────────────────────────────────────────────────────────────────
    const eventModal    = new bootstrap.Modal(document.getElementById('eventModal'));
    const quickAddModal = new bootstrap.Modal(document.getElementById('quickAddModal'));

    const statusBadges = {
        pending:   '<span class="badge bg-warning text-dark">Gözləyir</span>',
        confirmed: '<span class="badge bg-info">Təsdiqləndi</span>',
        completed: '<span class="badge bg-success">Tamamlandı</span>',
        cancelled: '<span class="badge bg-danger">Ləğv edildi</span>',
    };

    // ── Quick-add helpers ─────────────────────────────────────────────────────
    const storeUrl  = '{{ route('panel.appointments.store') }}';
    const searchUrl = '{{ route('panel.patients.search') }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function pad(n) { return String(n).padStart(2, '0'); }

    function openQuickAdd(date, timeStr) {
        const y = date.getFullYear();
        const m = pad(date.getMonth() + 1);
        const d = pad(date.getDate());
        const dateStr = y + '-' + m + '-' + d;

        document.getElementById('qa-date').value       = dateStr;
        document.getElementById('qa-time').value       = timeStr || '09:00';
        document.getElementById('qa-patient-id').value = '';
        document.getElementById('qa-patient-search').value = '';
        document.getElementById('qa-patient-dropdown').classList.add('d-none');
        document.getElementById('qa-patient-info').classList.add('d-none');
        document.getElementById('qa-error').classList.add('d-none');
        document.getElementById('qa-treatment').value  = '';
        document.getElementById('qa-duration').value   = '30';
        syncScheduledAt();

        eventModal._element && bootstrap.Modal.getInstance(document.getElementById('eventModal'))?.hide();
        quickAddModal.show();
    }

    function syncScheduledAt() {
        const d = document.getElementById('qa-date').value;
        const t = document.getElementById('qa-time').value;
        document.getElementById('qa-scheduled-at').value = d && t ? d + 'T' + t : '';
    }

    document.getElementById('qa-date').addEventListener('change', syncScheduledAt);
    document.getElementById('qa-time').addEventListener('change', syncScheduledAt);

    // Treatment type → auto-fill duration
    document.getElementById('qa-treatment').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const dur = opt.getAttribute('data-duration');
        if (dur) document.getElementById('qa-duration').value = dur;
    });

    // ── Patient autocomplete in modal ─────────────────────────────────────────
    let acTimer = null;
    const qaSearch   = document.getElementById('qa-patient-search');
    const qaDropdown = document.getElementById('qa-patient-dropdown');
    const qaInfo     = document.getElementById('qa-patient-info');

    qaSearch.addEventListener('input', function () {
        clearTimeout(acTimer);
        const q = this.value.trim();
        if (q.length < 2) { qaDropdown.classList.add('d-none'); return; }

        acTimer = setTimeout(() => {
            fetch(searchUrl + '?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(patients => {
                    if (!patients.length) { qaDropdown.classList.add('d-none'); return; }
                    qaDropdown.innerHTML = patients.map(p => {
                        const bd = p.birth_date ? p.birth_date.split('T')[0] : null;
                        const age = bd ? new Date().getFullYear() - new Date(bd).getFullYear() : null;
                        return `<div class="qa-patient-item"
                                     data-id="${p.id}" data-name="${p.name} ${p.surname}"
                                     data-phone="${p.phone ?? ''}" data-birth="${bd ?? ''}">
                                    <span class="fw-medium">${p.name} ${p.surname}</span>
                                    <small class="text-muted ms-2">${[p.phone, age ? age + ' yaş' : null].filter(Boolean).join(' · ')}</small>
                                </div>`;
                    }).join('');
                    qaDropdown.classList.remove('d-none');
                });
        }, 300);
    });

    document.addEventListener('mousedown', function (e) {
        const item = e.target.closest('.qa-patient-item');
        if (item) {
            document.getElementById('qa-patient-id').value = item.dataset.id;
            qaSearch.value = item.dataset.name;
            qaDropdown.classList.add('d-none');
            const parts = [];
            if (item.dataset.phone) parts.push('Tel: ' + item.dataset.phone);
            if (item.dataset.birth) {
                const age = new Date().getFullYear() - new Date(item.dataset.birth).getFullYear();
                parts.push('Doğum: ' + item.dataset.birth + ' (' + age + ' yaş)');
            }
            if (parts.length) {
                qaInfo.textContent = parts.join(' · ');
                qaInfo.classList.remove('d-none');
            }
            return;
        }
        if (!e.target.closest('#qa-patient-search') && !e.target.closest('#qa-patient-dropdown')) {
            qaDropdown.classList.add('d-none');
        }
    });

    qaSearch.addEventListener('blur', () => setTimeout(() => qaDropdown.classList.add('d-none'), 200));

    // ── Save quick add via AJAX ───────────────────────────────────────────────
    document.getElementById('qa-save').addEventListener('click', function () {
        const patientId   = document.getElementById('qa-patient-id').value;
        const scheduledAt = document.getElementById('qa-scheduled-at').value;

        if (!patientId) {
            showQaError('Zəhmət olmasa müştəri seçin.');
            qaSearch.focus();
            return;
        }
        if (!scheduledAt) {
            showQaError('Tarix və saat doldurulmalıdır.');
            return;
        }

        const form     = document.getElementById('qa-form');
        const formData = new FormData(form);
        formData.set('patient_id',   patientId);
        formData.set('scheduled_at', scheduledAt);

        const btnText    = document.getElementById('qa-save-text');
        const btnSpinner = document.getElementById('qa-save-spinner');
        btnText.classList.add('d-none');
        btnSpinner.classList.remove('d-none');
        document.getElementById('qa-save').disabled = true;

        const body = new URLSearchParams();
        formData.forEach((v, k) => body.append(k, v));

        fetch(storeUrl, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    body.toString(),
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (ok) {
                quickAddModal.hide();
                calendar.refetchEvents();
                showToast(data.message || 'Randevu yaradıldı.', 'success');
            } else {
                showQaError(data.error || data.message || 'Xəta baş verdi.');
            }
        })
        .catch(() => showQaError('Şəbəkə xətası. Yenidən cəhd edin.'))
        .finally(() => {
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
            document.getElementById('qa-save').disabled = false;
        });
    });

    function showQaError(msg) {
        const el = document.getElementById('qa-error');
        el.textContent = msg;
        el.classList.remove('d-none');
    }

    // ── Toast ─────────────────────────────────────────────────────────────────
    function showToast(msg, type) {
        const wrap = document.createElement('div');
        wrap.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;';
        wrap.innerHTML = `<div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 show" role="alert">
            <div class="d-flex"><div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div></div>`;
        document.body.appendChild(wrap);
        setTimeout(() => wrap.remove(), 4000);
    }

    // ── FAB ───────────────────────────────────────────────────────────────────
    document.getElementById('fab-add').addEventListener('click', function () {
        const today = new Date();
        openQuickAdd(today, pad(today.getHours()) + ':' + pad(today.getMinutes() >= 30 ? 30 : 0));
    });

    // ── FullCalendar ──────────────────────────────────────────────────────────
    const isMobile = window.innerWidth < 768;
    let businessHours = [];

    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        locale: 'az',
        initialView: isMobile ? 'listWeek' : 'timeGridWeek',
        headerToolbar: isMobile ? {
            left:   'prev,next',
            center: 'title',
            right:  'timeGridDay,listWeek'
        } : {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: { today: 'Bu gün', month: 'Ay', week: 'Həftə', day: 'Gün', list: 'Siyahı' },
        slotMinTime: '07:00:00',
        slotMaxTime: '21:00:00',
        allDaySlot: false,
        height: 'auto',
        nowIndicator: true,
        eventTimeFormat:  { hour: '2-digit', minute: '2-digit', hour12: false },
        slotLabelFormat:  { hour: '2-digit', minute: '2-digit', hour12: false },
        businessHours: businessHours,

        // "+" on column headers (timeGridWeek / timeGridDay)
        dayHeaderDidMount: function (info) {
            if (!info.date) return;
            const cushion = info.el.querySelector('.fc-col-header-cell-cushion');
            if (!cushion) return;
            const btn = document.createElement('button');
            btn.type      = 'button';
            btn.className = 'fc-add-btn';
            btn.innerHTML = '<i class="bi bi-plus-circle-fill"></i>';
            btn.title     = 'Randevu əlavə et';
            btn.addEventListener('click', function (e) {
                e.preventDefault(); e.stopPropagation();
                openQuickAdd(info.date, pad(new Date().getHours()) + ':' + pad(new Date().getMinutes() >= 30 ? 30 : 0));
            });
            cushion.appendChild(btn);
        },

        // "+" on day cells (dayGridMonth)
        dayCellDidMount: function (info) {
            const top = info.el.querySelector('.fc-daygrid-day-top');
            if (!top) return;
            const btn = document.createElement('button');
            btn.type      = 'button';
            btn.className = 'fc-add-btn';
            btn.innerHTML = '<i class="bi bi-plus-circle"></i>';
            btn.title     = 'Randevu əlavə et';
            btn.addEventListener('click', function (e) {
                e.preventDefault(); e.stopPropagation();
                openQuickAdd(info.date, '09:00');
            });
            top.appendChild(btn);
        },

        // Click on empty time slot
        dateClick: function (info) {
            const t = info.date.toTimeString().slice(0, 5);
            openQuickAdd(info.date, t === '00:00' ? '09:00' : t);
        },

        events: function (fetchInfo, successCallback, failureCallback) {
            fetch('{{ route('panel.calendar.events') }}?start=' + fetchInfo.startStr + '&end=' + fetchInfo.endStr)
                .then(r => r.json())
                .then(data => {
                    if (data.businessHours?.length) calendar.setOption('businessHours', data.businessHours);
                    successCallback(data.events || data);
                })
                .catch(failureCallback);
        },

        eventClick: function (info) {
            const p = info.event.extendedProps;
            const start = info.event.start;
            const datetimeStr = start
                ? start.toLocaleDateString('az-Latn-AZ') + ' ' + start.toLocaleTimeString('az-Latn-AZ', {hour:'2-digit',minute:'2-digit',hour12:false})
                : '—';
            const end = info.event.end;
            const durationMin = (start && end) ? Math.round((end - start) / 60000) : null;

            document.getElementById('modal-patient').textContent   = p.patient_name   || '—';
            document.getElementById('modal-treatment').textContent = p.treatment_type || '—';
            document.getElementById('modal-datetime').textContent  = datetimeStr;
            document.getElementById('modal-duration').textContent  = durationMin ? durationMin + ' dəqiqə' : '—';
            document.getElementById('modal-status').innerHTML      = statusBadges[p.status] || p.status || '—';

            const notesBlock = document.getElementById('modal-notes-block');
            if (p.notes) {
                notesBlock.style.display = 'block';
                document.getElementById('modal-notes').textContent = p.notes;
            } else {
                notesBlock.style.display = 'none';
            }
            document.getElementById('modal-link').href = p.appointment_url || '#';
            eventModal.show();
        },

        eventDidMount: function (info) {
            info.el.title = info.event.title;
        }
    });

    calendar.render();
});
</script>
@endpush
