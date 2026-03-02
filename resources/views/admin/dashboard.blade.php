@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-person-badge fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $stats['total_doctors'] }}</div>
                    <div class="text-muted small">Cəmi İstifadəçilər</div>
                    <div class="text-success small">{{ $stats['active_doctors'] }} aktiv</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-people fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $stats['total_patients'] }}</div>
                    <div class="text-muted small">Cəmi Müştərilər</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="bi bi-calendar-check fs-4 text-info"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $stats['today_appointments'] }}</div>
                    <div class="text-muted small">Bu günkü Randevular</div>
                    <div class="text-info small">{{ $stats['total_appointments'] }} cəmi</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-chat-dots fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $stats['total_sms'] }}</div>
                    <div class="text-muted small">Göndərilən SMS</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Son Qeydiyyatlı İstifadəçilər</h6>
                <a href="{{ route('admin.doctors.index') }}" class="btn btn-sm btn-outline-primary">Hamısı</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ad Soyad</th>
                                <th>İxtisas</th>
                                <th>Abunəlik</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDoctors as $doctor)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.doctors.show', $doctor) }}" class="text-decoration-none fw-medium">
                                        {{ $doctor->full_name }}
                                    </a>
                                    <div class="text-muted small">{{ $doctor->email }}</div>
                                </td>
                                <td>{{ $doctor->specialty?->name ?? '—' }}</td>
                                <td>
                                    @if($doctor->activeSubscription)
                                        <span class="badge bg-success">{{ $doctor->activeSubscription->package->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">Yoxdur</span>
                                    @endif
                                </td>
                                <td>
                                    @if($doctor->is_active)
                                        <span class="badge bg-success">Aktiv</span>
                                    @else
                                        <span class="badge bg-danger">Deaktiv</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">İstifadəçi tapılmadı</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold text-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>Bitmək Üzrə Abunəliklər (7 gün)
                </h6>
            </div>
            <div class="card-body p-0">
                @forelse($expiringSubscriptions as $sub)
                <div class="p-3 border-bottom">
                    <div class="fw-medium">{{ $sub->doctor->full_name }}</div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-muted small">{{ $sub->package->name }}</span>
                        <span class="badge bg-warning text-dark">{{ $sub->expires_at->format('d.m.Y') }}</span>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>
                    Bitmək üzrə abunəlik yoxdur
                </div>
                @endforelse
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Sürətli Əməliyyatlar</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.doctors.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus me-2"></i>Yeni İstifadəçi Əlavə Et
                    </a>
                    <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-outline-success">
                        <i class="bi bi-credit-card me-2"></i>Abunəlik Ver
                    </a>
                    <a href="{{ route('admin.packages.create') }}" class="btn btn-outline-info">
                        <i class="bi bi-box-seam me-2"></i>Yeni Paket Yarat
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
