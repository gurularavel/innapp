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
    .fc-toolbar-title {
        font-size: 1.1rem !important;
        font-weight: 600;
    }
    .fc-event {
        cursor: pointer;
        border: none !important;
    }
    .fc-event-title {
        font-weight: 500;
        padding: 0 2px;
    }

    /* ── Mobile responsive ───────────────────────────── */
    @media (max-width: 767.98px) {
        #calendar {
            padding: .5rem;
        }
        .fc-toolbar-title {
            font-size: .9rem !important;
        }
        .fc .fc-toolbar {
            flex-wrap: wrap;
            gap: .35rem;
        }
        .fc .fc-toolbar-chunk {
            display: flex;
            flex-wrap: wrap;
            gap: .25rem;
        }
        .fc .fc-button {
            font-size: .75rem !important;
            padding: .2rem .45rem !important;
        }
        .fc .fc-button-group .fc-button {
            padding: .2rem .4rem !important;
        }
        /* List view rows — more compact */
        .fc-list-event td {
            padding: .35rem .5rem !important;
            font-size: .82rem;
        }
        .fc-list-day-cushion {
            font-size: .82rem !important;
            padding: .3rem .5rem !important;
        }
        /* Day/week grid — shrink time gutter */
        .fc-timegrid-slot-label {
            font-size: .72rem !important;
        }
        .fc-col-header-cell-cushion {
            font-size: .78rem !important;
        }
        .fc-event-title {
            font-size: .75rem;
        }
        .fc-event-time {
            font-size: .72rem;
        }
    }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-sm-3 p-0">
        <div id="calendar"></div>
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
    const calendarEl = document.getElementById('calendar');
    const eventModal  = new bootstrap.Modal(document.getElementById('eventModal'));

    const statusBadges = {
        pending:   '<span class="badge bg-warning text-dark">Gözləyir</span>',
        confirmed: '<span class="badge bg-info">Təsdiqləndi</span>',
        completed: '<span class="badge bg-success">Tamamlandı</span>',
        cancelled: '<span class="badge bg-danger">Ləğv edildi</span>',
    };

    let businessHours = [];
    const isMobile = window.innerWidth < 768;

    const calendar = new FullCalendar.Calendar(calendarEl, {
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
        buttonText: {
            today:  'Bu gün',
            month:  'Ay',
            week:   'Həftə',
            day:    'Gün',
            list:   'Siyahı',
        },
        slotMinTime: '07:00:00',
        slotMaxTime: '21:00:00',
        allDaySlot: false,
        height: 'auto',
        nowIndicator: true,
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
            meridiem: false
        },
        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
            meridiem: false
        },
        businessHours: businessHours,
        events: function (fetchInfo, successCallback, failureCallback) {
            fetch('{{ route('panel.calendar.events') }}?start=' + fetchInfo.startStr + '&end=' + fetchInfo.endStr)
                .then(r => r.json())
                .then(data => {
                    // Update businessHours dynamically
                    if (data.businessHours && data.businessHours.length) {
                        calendar.setOption('businessHours', data.businessHours);
                    }
                    successCallback(data.events || data);
                })
                .catch(failureCallback);
        },
        eventClick: function (info) {
            const p = info.event.extendedProps;

            // Compute datetime from event start
            const start = info.event.start;
            const datetimeStr = start
                ? start.toLocaleDateString('az-Latn-AZ') + ' ' + start.toLocaleTimeString('az-Latn-AZ', {hour: '2-digit', minute: '2-digit', hour12: false})
                : '—';

            // Compute duration from start/end difference
            const end = info.event.end;
            const durationMin = (start && end) ? Math.round((end - start) / 60000) : null;

            document.getElementById('modal-patient').textContent   = p.patient_name    || '—';
            document.getElementById('modal-treatment').textContent = p.treatment_type  || '—';
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
