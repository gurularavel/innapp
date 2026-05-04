@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3" style="border-top:3px solid #1a85d9;border-radius:.65rem .65rem 0 0;">
                <div class="stat-icon" style="background:#e8f4ff;color:#1a85d9;">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['total_doctors'] }}</div>
                    <div class="stat-label">Cəmi İstifadəçilər</div>
                    <div style="font-size:.75rem;color:#1db87a;margin-top:.15rem;">{{ $stats['active_doctors'] }} aktiv</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3" style="border-top:3px solid #1db87a;border-radius:.65rem .65rem 0 0;">
                <div class="stat-icon" style="background:#e4f9f0;color:#1db87a;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['total_patients'] }}</div>
                    <div class="stat-label">Cəmi Müştərilər</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3" style="border-top:3px solid #00b4d8;border-radius:.65rem .65rem 0 0;">
                <div class="stat-icon" style="background:#e0f7fd;color:#00b4d8;">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['today_appointments'] }}</div>
                    <div class="stat-label">Bu Günkü Randevular</div>
                    <div style="font-size:.75rem;color:#00b4d8;margin-top:.15rem;">{{ $stats['total_appointments'] }} cəmi</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3" style="border-top:3px solid #e84393;border-radius:.65rem .65rem 0 0;">
                <div class="stat-icon" style="background:#fde8f2;color:#e84393;">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['total_sms'] }}</div>
                    <div class="stat-label">Göndərilən SMS</div>
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
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">Hamısı</a>
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
                                    <a href="{{ route('admin.users.show', $doctor) }}" class="text-decoration-none fw-medium">
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
                    <a href="{{ route('admin.users.create') }}" class="btn btn-outline-primary">
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
