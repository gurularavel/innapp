@extends('layouts.doctor')

@section('title', 'Müştəri Profili')
@section('page-title', 'Müştəri Profili')

@section('content')
<div class="d-flex justify-content-end align-items-center mb-4 gap-2 flex-wrap">
    <a href="{{ route('panel.appointments.create') }}?patient_id={{ $patient->id }}"
       class="btn btn-success btn-sm">
        <i class="bi bi-calendar-plus me-1"></i><span class="d-none d-sm-inline">Randevu Əlavə Et</span><span class="d-sm-none">Randevu</span>
    </a>
    <a href="{{ route('panel.patients.visits.create', $patient) }}" class="btn btn-outline-info btn-sm">
        <i class="bi bi-clock-history me-1"></i><span class="d-none d-sm-inline">Ziyarət Əlavə Et</span><span class="d-sm-none">Ziyarət</span>
    </a>
    <a href="{{ route('panel.patients.edit', $patient) }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil me-1"></i>Düzəlt
    </a>
    <a href="{{ route('panel.patients.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Geri
    </a>
</div>

<div class="row g-4">
    {{-- Patient Info --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                @if($patient->photo_url)
                    <img src="{{ $patient->photo_url }}" alt="{{ $patient->full_name }}"
                         class="rounded-circle mx-auto d-block mb-3"
                         style="width:80px;height:80px;object-fit:cover;border:3px solid #e9ecef;">
                @else
                    <div class="rounded-circle bg-success bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width:80px;height:80px;">
                        <i class="bi bi-person fs-2 text-success"></i>
                    </div>
                @endif
                <h5 class="fw-bold mb-1">{{ $patient->full_name }}</h5>
                @php $genderLabels = ['male' => 'Kişi', 'female' => 'Qadın', 'other' => 'Digər']; @endphp
                <div class="text-muted">{{ $genderLabels[$patient->gender] ?? '—' }}</div>
            </div>
            <div class="card-body border-top pt-3">
                <div class="mb-3">
                    <div class="text-muted small">Telefon</div>
                    <div class="fw-medium">{{ $patient->phone ?? '—' }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Doğum Tarixi</div>
                    <div class="fw-medium">
                        {{ $patient->birth_date ? $patient->birth_date->format('d.m.Y') : '—' }}
                        @if($patient->birth_date)
                            <span class="text-muted small">({{ $patient->birth_date->age }} yaş)</span>
                        @endif
                    </div>
                </div>
                @if($patient->weight)
                <div class="mb-3">
                    <div class="text-muted small">Çəki</div>
                    <div class="fw-medium">{{ $patient->weight }} kg</div>
                </div>
                @endif
                @if($patient->blood_type)
                <div class="mb-3">
                    <div class="text-muted small">Qan Qrupu</div>
                    <div class="fw-medium">{{ $patient->blood_type }}</div>
                </div>
                @endif
                @if($patient->marital_status)
                <div class="mb-3">
                    <div class="text-muted small">Ailə Vəziyyəti</div>
                    <div class="fw-medium">{{ $patient->marital_status_label }}</div>
                </div>
                @endif
                <div class="mb-3">
                    <div class="text-muted small">Qeydiyyat Tarixi</div>
                    <div class="fw-medium">{{ $patient->created_at->format('d.m.Y') }}</div>
                </div>
                @if($patient->notes)
                <div>
                    <div class="text-muted small">Qeydlər</div>
                    <div class="fw-medium small mt-1 p-2 bg-light rounded">{{ $patient->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right Column: Tabs --}}
    <div class="col-lg-8">

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-0" id="patientTabs" style="border-bottom:none;">
            <li class="nav-item">
                <button class="nav-link active fw-medium" id="tab-history" data-bs-toggle="tab" data-bs-target="#pane-history" type="button">
                    <i class="bi bi-clock-history me-1"></i>Tibb Tarixi
                    <span class="badge bg-info ms-1">{{ $patient->visits->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-medium" id="tab-appointments" data-bs-toggle="tab" data-bs-target="#pane-appointments" type="button">
                    <i class="bi bi-calendar3 me-1"></i>Randevular
                    <span class="badge bg-secondary ms-1">{{ $patient->appointments->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content">
            {{-- ===== VISIT HISTORY ===== --}}
            <div class="tab-pane fade show active" id="pane-history">
                <div class="card border-0 shadow-sm" style="border-top-left-radius:0;">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Tibb Tarixi</span>
                        <a href="{{ route('panel.patients.visits.create', $patient) }}" class="btn btn-sm btn-info text-white">
                            <i class="bi bi-plus-lg me-1"></i>Yeni Ziyarət
                        </a>
                    </div>

                    @if($patient->visits->isEmpty())
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-clock-history fs-1 d-block mb-2 opacity-25"></i>
                        Tibb tarixi tapılmadı
                        <div class="mt-2">
                            <a href="{{ route('panel.patients.visits.create', $patient) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-plus-lg me-1"></i>İlk ziyarəti əlavə et
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="accordion accordion-flush" id="visitsAccordion">
                        @foreach($patient->visits as $visit)
                        <div class="accordion-item border-bottom">
                            <div class="accordion-header d-flex align-items-center pe-2">
                                {{-- Collapsible trigger --}}
                                <button class="accordion-button collapsed py-3 flex-grow-1"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#visit-body-{{ $visit->id }}"
                                        aria-expanded="false">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="text-muted small" style="min-width:110px;">
                                            <i class="bi bi-calendar3 me-1 text-info"></i>{{ $visit->visited_at->format('d.m.Y') }}
                                            <span class="text-muted">{{ $visit->visited_at->format('H:i') }}</span>
                                        </span>
                                        <span class="fw-semibold text-dark">
                                            {{ $visit->title ?: '—' }}
                                        </span>
                                        @if($visit->files->isNotEmpty())
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border" style="font-size:.7rem;">
                                            <i class="bi bi-paperclip me-1"></i>{{ $visit->files->count() }}
                                        </span>
                                        @endif
                                    </div>
                                </button>
                                {{-- Action buttons always visible --}}
                                <div class="d-flex gap-1 ms-2 flex-shrink-0">
                                    <a href="{{ route('panel.patients.visits.edit', [$patient, $visit]) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('panel.patients.visits.destroy', [$patient, $visit]) }}"
                                          onsubmit="return confirm('Bu ziyarəti silmək istəyirsiniz?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div id="visit-body-{{ $visit->id }}" class="accordion-collapse collapse"
                                 data-bs-parent="">
                                <div class="accordion-body pt-2 pb-3">
                                    {{-- Notes --}}
                                    @if($visit->notes)
                                    <div class="small text-muted bg-light rounded p-2 mb-3">{{ $visit->notes }}</div>
                                    @endif

                                    {{-- Files --}}
                                    @if($visit->files->isNotEmpty())
                                    <div class="row g-2">
                                        @foreach($visit->files as $file)
                                        <div class="col-6 col-sm-4 col-md-3">
                                            @if($file->is_image)
                                            <img src="{{ $file->url }}"
                                                 alt="{{ $file->original_name }}"
                                                 class="img-fluid rounded border visit-img"
                                                 style="width:100%;height:90px;object-fit:cover;cursor:zoom-in;"
                                                 data-src="{{ $file->url }}"
                                                 data-name="{{ $file->original_name }}">
                                            @else
                                            <a href="{{ $file->url }}" target="_blank"
                                               class="d-flex align-items-center gap-2 p-2 border rounded text-decoration-none text-dark bg-white h-100">
                                                <i class="bi bi-file-earmark-pdf text-danger fs-4 flex-shrink-0"></i>
                                                <span class="small text-truncate">{{ $file->original_name }}</span>
                                            </a>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @elseif(!$visit->notes)
                                    <div class="text-muted small">Məlumat yoxdur.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Image lightbox modal --}}
            <div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content bg-transparent border-0">
                        <div class="modal-header border-0 pb-1 px-2">
                            <span class="text-white small" id="imgModalName"></span>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center p-0">
                            <img id="imgModalSrc" src="" alt="" class="img-fluid rounded" style="max-height:85vh;">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== APPOINTMENTS ===== --}}
            <div class="tab-pane fade" id="pane-appointments">
                <div class="card border-0 shadow-sm" style="border-top-left-radius:0;">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Randevu Tarixçəsi</span>
                        <span class="badge bg-secondary">{{ $patient->appointments->count() }}</span>
                    </div>
                    @php
                        $apts   = $patient->appointments()->with('treatmentType')->latest('scheduled_at')->get();
                        $badges = ['pending'=>'warning','confirmed'=>'info','completed'=>'success','cancelled'=>'danger'];
                        $labels = ['pending'=>'Gözləyir','confirmed'=>'Təsdiqləndi','completed'=>'Tamamlandı','cancelled'=>'Ləğv edildi'];
                    @endphp

                    {{-- Mobile --}}
                    <div class="d-md-none">
                        @forelse($apts as $apt)
                        <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small fw-medium">{{ $apt->scheduled_at->format('d.m.Y H:i') }}</div>
                                <div class="text-muted" style="font-size:.78rem;">{{ $apt->treatmentType?->name ?? '—' }} · {{ $apt->duration_minutes }} dəq</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ $badges[$apt->status] ?? 'secondary' }}">{{ $labels[$apt->status] ?? $apt->status }}</span>
                                <a href="{{ route('panel.appointments.show', $apt) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">Randevu tapılmadı</div>
                        @endforelse
                    </div>

                    {{-- Desktop --}}
                    <div class="card-body p-0 d-none d-md-block">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Tarix</th><th>Müalicə</th><th>Müddət</th><th>Status</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @forelse($apts as $apt)
                                    <tr>
                                        <td class="text-muted small">{{ $apt->scheduled_at->format('d.m.Y H:i') }}</td>
                                        <td>{{ $apt->treatmentType?->name ?? '—' }}</td>
                                        <td class="text-muted small">{{ $apt->duration_minutes }} dəq</td>
                                        <td>
                                            <span class="badge bg-{{ $badges[$apt->status] ?? 'secondary' }}">
                                                {{ $labels[$apt->status] ?? $apt->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('panel.appointments.show', $apt) }}" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">Randevu tapılmadı</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- end tab-content --}}
    </div>
</div>
@endsection

@push('styles')
<style>
#imgModal .modal-content { background: rgba(0,0,0,.85); }
.visit-img:hover { opacity:.88; }
.accordion-button:not(.collapsed) { background:#f0f8ff; color:inherit; box-shadow:none; }
.accordion-button::after { flex-shrink:0; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const modal    = new bootstrap.Modal(document.getElementById('imgModal'));
    const modalImg = document.getElementById('imgModalSrc');
    const modalName = document.getElementById('imgModalName');

    document.querySelectorAll('.visit-img').forEach(img => {
        img.addEventListener('click', function () {
            modalImg.src      = this.dataset.src;
            modalName.textContent = this.dataset.name;
            modal.show();
        });
    });
})();
</script>
@endpush
