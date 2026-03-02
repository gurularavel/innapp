@extends('layouts.doctor')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Subscription warning --}}
@php $subscription = auth()->user()->activeSubscription()->with('package')->first(); @endphp
@if(!$subscription)
<div class="alert alert-warning d-flex align-items-center mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
    <div>
        <strong>Aktiv abunəliyiniz yoxdur!</strong>
        Sistemi tam istifadə etmək üçün abunəliyə ehtiyacınız var.
        Zəhmət olmasa administratorla əlaqə saxlayın.
    </div>
</div>
@endif

{{-- Stats --}}
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-people fs-4 text-primary"></i>
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
                    <i class="bi bi-calendar-day fs-4 text-info"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $stats['today_appointments'] }}</div>
                    <div class="text-muted small">Bu günkü Randevular</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $stats['pending_appointments'] }}</div>
                    <div class="text-muted small">Gözləyən Randevular</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-calendar-check fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ $stats['this_month_appointments'] }}</div>
                    <div class="text-muted small">Bu Ay Randevular</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Today's Appointments --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-calendar-day me-2 text-info"></i>Bu Günkü Randevular
                </h6>
                <a href="{{ route('doctor.appointments.index') }}?date={{ now()->format('Y-m-d') }}"
                   class="btn btn-sm btn-outline-info">Hamısı</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Müştəri</th>
                                <th>Saat</th>
                                <th>Xidmət</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todayAppointments as $apt)
                            <tr>
                                <td>
                                    <a href="{{ route('doctor.patients.show', $apt->patient) }}" class="text-decoration-none fw-medium">
                                        {{ $apt->patient->full_name }}
                                    </a>
                                </td>
                                <td class="text-muted small">{{ $apt->scheduled_at->format('H:i') }}</td>
                                <td class="text-muted small">{{ $apt->treatmentType?->name ?? '—' }}</td>
                                <td>
                                    @php
                                        $badges = ['pending'=>'warning','confirmed'=>'info','completed'=>'success','cancelled'=>'danger'];
                                        $labels = ['pending'=>'Gözləyir','confirmed'=>'Təsdiqləndi','completed'=>'Tamamlandı','cancelled'=>'Ləğv edildi'];
                                    @endphp
                                    <span class="badge bg-{{ $badges[$apt->status] ?? 'secondary' }}">
                                        {{ $labels[$apt->status] ?? $apt->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Bu gün randevu yoxdur</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div class="col-lg-5">
        {{-- Subscription info --}}
        @if($subscription)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-credit-card me-2 text-success"></i>Abunəlik
                </h6>
            </div>
            <div class="card-body">
                <div class="fw-semibold fs-5 mb-1">{{ $subscription->package->name }}</div>
                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <div class="text-muted small">Müştəri İstifadəsi</div>
                        <div class="fw-medium">
                            {{ $subscription->patients_used }}/{{ $subscription->package->patient_limit ?? '∞' }}
                        </div>
                        @if($subscription->package->patient_limit)
                        <div class="progress mt-1" style="height:4px;">
                            <div class="progress-bar bg-primary" style="width:{{ min(100, ($subscription->patients_used / $subscription->package->patient_limit) * 100) }}%"></div>
                        </div>
                        @endif
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">SMS İstifadəsi</div>
                        <div class="fw-medium">
                            {{ $subscription->sms_used }}/{{ $subscription->package->sms_limit ?? '∞' }}
                        </div>
                        @if($subscription->package->sms_limit)
                        <div class="progress mt-1" style="height:4px;">
                            <div class="progress-bar bg-warning" style="width:{{ min(100, ($subscription->sms_used / $subscription->package->sms_limit) * 100) }}%"></div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Bitmə tarixi</span>
                    <span class="badge {{ $subscription->expires_at->diffInDays() <= 7 ? 'bg-warning text-dark' : 'bg-success' }}">
                        {{ $subscription->expires_at->format('d.m.Y') }}
                    </span>
                </div>
            </div>
        </div>
        @endif

        {{-- Upcoming appointments --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-calendar-week me-2 text-primary"></i>Yaxınlaşan Randevular
                </h6>
                <a href="{{ route('doctor.appointments.index') }}" class="btn btn-sm btn-outline-primary">Hamısı</a>
            </div>
            <div class="card-body p-0">
                @forelse($upcomingAppointments as $apt)
                <a href="{{ route('doctor.appointments.show', $apt) }}"
                   class="d-block px-3 py-2 border-bottom text-decoration-none text-dark hover-bg">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-medium small">{{ $apt->patient->full_name }}</div>
                            <div class="text-muted" style="font-size:.75rem;">{{ $apt->treatmentType?->name ?? '—' }}</div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">{{ $apt->scheduled_at->format('d.m') }}</div>
                            <div class="text-muted" style="font-size:.75rem;">{{ $apt->scheduled_at->format('H:i') }}</div>
                        </div>
                    </div>
                </a>
                @empty
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                    Yaxınlaşan randevu yoxdur
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
