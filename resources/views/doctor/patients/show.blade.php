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
                    <div class="card-body p-3">
                        @forelse($patient->visits as $visit)
                        <div class="visit-entry mb-4 pb-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                            {{-- Header --}}
                            <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1">
                                            <i class="bi bi-calendar3 me-1"></i>{{ $visit->visited_at->format('d.m.Y H:i') }}
                                        </span>
                                        @if($visit->title)
                                        <span class="fw-semibold">{{ $visit->title }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
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
                                    <a href="{{ $file->url }}" target="_blank" class="d-block">
                                        <img src="{{ $file->url }}" alt="{{ $file->original_name }}"
                                             class="img-fluid rounded border"
                                             style="width:100%;height:90px;object-fit:cover;">
                                    </a>
                                    @else
                                    <a href="{{ $file->url }}" target="_blank"
                                       class="d-flex align-items-center gap-2 p-2 border rounded text-decoration-none text-dark bg-white">
                                        <i class="bi bi-file-earmark-pdf text-danger fs-4"></i>
                                        <span class="small text-truncate">{{ $file->original_name }}</span>
                                    </a>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-clock-history fs-1 d-block mb-2 opacity-25"></i>
                            Tibb tarixi tapılmadı
                            <div class="mt-2">
                                <a href="{{ route('panel.patients.visits.create', $patient) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-plus-lg me-1"></i>İlk ziyarəti əlavə et
                                </a>
                            </div>
                        </div>
                        @endforelse
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
