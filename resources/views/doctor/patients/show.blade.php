@extends('layouts.doctor')

@section('title', 'Müştəri Profili')
@section('page-title', 'Müştəri Profili')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('doctor.appointments.create') }}?patient_id={{ $patient->id }}"
           class="btn btn-success btn-sm">
            <i class="bi bi-calendar-plus me-1"></i>Randevu Əlavə Et
        </a>
        <a href="{{ route('doctor.patients.edit', $patient) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i>Düzəlt
        </a>
        <a href="{{ route('doctor.patients.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Geri
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Patient Info --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-success bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3"
                     style="width:80px;height:80px;">
                    <i class="bi bi-person fs-2 text-success"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $patient->full_name }}</h5>
                @php
                    $genderLabels = ['male' => 'Kişi', 'female' => 'Qadın', 'other' => 'Digər'];
                @endphp
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

    {{-- Appointments History --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Randevu Tarixçəsi</h6>
                <span class="badge bg-secondary">{{ $patient->appointments->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tarix</th>
                                <th>Müalicə</th>
                                <th>Müddət</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($patient->appointments()->with('treatmentType')->latest('scheduled_at')->get() as $apt)
                            <tr>
                                <td class="text-muted small">{{ $apt->scheduled_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $apt->treatmentType?->name ?? '—' }}</td>
                                <td class="text-muted small">{{ $apt->duration_minutes }} dəq</td>
                                <td>
                                    @php
                                        $badges = ['pending'=>'warning','confirmed'=>'info','completed'=>'success','cancelled'=>'danger'];
                                        $labels = ['pending'=>'Gözləyir','confirmed'=>'Təsdiqləndi','completed'=>'Tamamlandı','cancelled'=>'Ləğv edildi'];
                                    @endphp
                                    <span class="badge bg-{{ $badges[$apt->status] ?? 'secondary' }}">
                                        {{ $labels[$apt->status] ?? $apt->status }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('doctor.appointments.show', $apt) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Randevu tapılmadı</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
