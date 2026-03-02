@extends('layouts.admin')

@section('title', 'İstifadəçi Profili')
@section('page-title', 'İstifadəçi Profili')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i>Düzəlt
        </a>
        <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Geri
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Doctor Info --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 mx-auto d-flex align-items-center justify-content-center mb-3"
                     style="width:80px;height:80px;">
                    <i class="bi bi-person-badge fs-2 text-primary"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $doctor->full_name }}</h5>
                <div class="text-muted">{{ $doctor->specialty?->name ?? 'İxtisas yoxdur' }}</div>
                <div class="mt-2">
                    @if($doctor->is_active)
                        <span class="badge bg-success">Aktiv</span>
                    @else
                        <span class="badge bg-danger">Deaktiv</span>
                    @endif
                </div>
            </div>
            <div class="card-body border-top pt-3">
                <div class="row g-2 text-sm">
                    <div class="col-12">
                        <div class="text-muted small">Email</div>
                        <div class="fw-medium">{{ $doctor->email }}</div>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="text-muted small">Telefon</div>
                        <div class="fw-medium">{{ $doctor->phone ?? '—' }}</div>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="text-muted small">Qeydiyyat tarixi</div>
                        <div class="fw-medium">{{ $doctor->created_at->format('d.m.Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="row g-3 mt-1">
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-3 fw-bold text-primary">{{ $patientsCount }}</div>
                    <div class="text-muted small">Müştəri</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-3 fw-bold text-success">{{ $appointmentsCount }}</div>
                    <div class="text-muted small">Randevu</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Subscriptions --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Abunəliklər</h6>
                <a href="{{ route('admin.subscriptions.create') }}?doctor_id={{ $doctor->id }}"
                   class="btn btn-sm btn-outline-success">
                    <i class="bi bi-plus-lg me-1"></i>Yeni Abunəlik
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Paket</th>
                                <th>Başlanğıc</th>
                                <th>Bitmə</th>
                                <th>Müştəri</th>
                                <th>SMS</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($doctor->subscriptions()->with('package')->latest()->get() as $sub)
                            <tr>
                                <td class="fw-medium">{{ $sub->package->name }}</td>
                                <td class="text-muted small">{{ $sub->starts_at->format('d.m.Y') }}</td>
                                <td class="text-muted small">{{ $sub->expires_at->format('d.m.Y') }}</td>
                                <td class="text-muted small">
                                    {{ $sub->patients_used }}/{{ $sub->package->patient_limit ?? '∞' }}
                                </td>
                                <td class="text-muted small">
                                    {{ $sub->sms_used }}/{{ $sub->package->sms_limit ?? '∞' }}
                                </td>
                                <td>
                                    @if($sub->is_active && $sub->expires_at->isFuture())
                                        <span class="badge bg-success">Aktiv</span>
                                    @elseif($sub->expires_at->isPast())
                                        <span class="badge bg-secondary">Bitib</span>
                                    @else
                                        <span class="badge bg-danger">Deaktiv</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Abunəlik tapılmadı</td>
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
