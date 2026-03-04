@extends('layouts.doctor')

@section('title', 'Abunəlik')
@section('page-title', 'Abunəlik')

@push('styles')
<style>
    .plan-card { border: 2px solid #e2e8f0; border-radius: .75rem; transition: border-color .2s, box-shadow .2s; }
    .plan-card:hover { border-color: #3788d8; box-shadow: 0 4px 20px rgba(55,136,216,.15); }
    .plan-card.featured { border-color: #3788d8; }
    .plan-badge { font-size: .7rem; padding: .25rem .6rem; border-radius: 999px; }
    .usage-bar-wrap { border-radius: .5rem; overflow: hidden; }
    .days-ring { width: 88px; height: 88px; }
    .history-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════════
     CURRENT SUBSCRIPTION
═══════════════════════════════════════════════ --}}
@if($current)
@php
    $daysTotal     = $current->starts_at->diffInDays($current->expires_at) ?: 1;
    $daysLeft      = max(0, (int) now()->diffInDays($current->expires_at, false));
    $daysProgress  = min(100, round($daysLeft / $daysTotal * 100));
    $patientLimit  = $current->package->patient_limit;
    $smsLimit      = $current->package->sms_limit;
    $patientPct    = $patientLimit ? min(100, round($current->patients_used / $patientLimit * 100)) : 0;
    $smsPct        = $smsLimit    ? min(100, round($current->sms_used    / $smsLimit    * 100)) : 0;
    $isExpiringSoon = $daysLeft <= 7;
@endphp

<div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #3788d8 !important; border-left-width: 4px;">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-check fs-5 text-primary"></i>
            <h6 class="mb-0 fw-semibold">Aktiv Abunəliyiniz</h6>
            <span class="badge bg-success">Aktiv</span>
            @if($isExpiringSoon)
                <span class="badge bg-danger">Son {{ $daysLeft }} gün!</span>
            @endif
        </div>
        <div class="text-muted small">
            {{ $current->starts_at->format('d.m.Y') }} → {{ $current->expires_at->format('d.m.Y') }}
        </div>
    </div>
    <div class="card-body">
        <div class="row g-4 align-items-center">
            {{-- Package name & days ring --}}
            <div class="col-md-4 d-flex align-items-center gap-3">
                <div class="position-relative days-ring">
                    <svg viewBox="0 0 88 88" class="w-100 h-100" style="transform:rotate(-90deg)">
                        <circle cx="44" cy="44" r="36" fill="none" stroke="#e9ecef" stroke-width="8"/>
                        <circle cx="44" cy="44" r="36" fill="none"
                                stroke="{{ $isExpiringSoon ? '#dc3545' : '#3788d8' }}"
                                stroke-width="8"
                                stroke-dasharray="{{ round(2 * 3.14159 * 36) }}"
                                stroke-dashoffset="{{ round(2 * 3.14159 * 36 * (1 - $daysProgress / 100)) }}"
                                stroke-linecap="round"/>
                    </svg>
                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                        <div class="fw-bold lh-1" style="font-size:1.15rem;color:{{ $isExpiringSoon ? '#dc3545' : '#3788d8' }}">{{ $daysLeft }}</div>
                        <div class="text-muted" style="font-size:.65rem">gün</div>
                    </div>
                </div>
                <div>
                    <div class="fw-bold fs-5">{{ $current->package->name }}</div>
                    <div class="text-muted small">{{ $current->package->price }} ₼ / dövr</div>
                    <div class="text-muted" style="font-size:.75rem">{{ $daysTotal }} günlük paket</div>
                </div>
            </div>

            {{-- Usage bars --}}
            <div class="col-md-5">
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-medium"><i class="bi bi-people me-1 text-primary"></i>Müştəri</span>
                        <span class="{{ $patientLimit && $patientPct >= 90 ? 'text-danger fw-semibold' : 'text-muted' }}">
                            {{ $current->patients_used }} / {{ $patientLimit ?? '∞' }}
                        </span>
                    </div>
                    <div class="progress usage-bar-wrap" style="height:8px">
                        @if($patientLimit)
                            <div class="progress-bar {{ $patientPct >= 90 ? 'bg-danger' : ($patientPct >= 70 ? 'bg-warning' : 'bg-primary') }}"
                                 style="width:{{ $patientPct }}%"></div>
                        @else
                            <div class="progress-bar bg-success" style="width:100%"></div>
                        @endif
                    </div>
                    @if(!$patientLimit)
                        <div class="text-success" style="font-size:.72rem">Limitsiz</div>
                    @endif
                </div>
                <div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-medium"><i class="bi bi-chat-dots me-1 text-info"></i>SMS</span>
                        <span class="{{ $smsLimit && $smsPct >= 90 ? 'text-danger fw-semibold' : 'text-muted' }}">
                            {{ $current->sms_used }} / {{ $smsLimit ?? '∞' }}
                        </span>
                    </div>
                    <div class="progress usage-bar-wrap" style="height:8px">
                        @if($smsLimit)
                            <div class="progress-bar {{ $smsPct >= 90 ? 'bg-danger' : ($smsPct >= 70 ? 'bg-warning' : 'bg-info') }}"
                                 style="width:{{ $smsPct }}%"></div>
                        @else
                            <div class="progress-bar bg-success" style="width:100%"></div>
                        @endif
                    </div>
                    @if(!$smsLimit)
                        <div class="text-success" style="font-size:.72rem">Limitsiz</div>
                    @endif
                </div>
            </div>

            {{-- Action --}}
            <div class="col-md-3 text-md-end">
                @if($isExpiringSoon)
                    <div class="alert alert-warning py-2 px-3 small mb-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Abunəliyin bitmək üzrədir!
                    </div>
                @endif
                <a href="#packages" class="btn btn-primary">
                    <i class="bi bi-arrow-up-circle me-1"></i>Uzat / Yüksəlt
                </a>
            </div>
        </div>
    </div>
</div>

@else
{{-- No subscription --}}
<div class="alert alert-warning d-flex align-items-center gap-3 mb-4 shadow-sm">
    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
    <div>
        <div class="fw-semibold">Aktiv abunəliyiniz yoxdur</div>
        <div class="small">Xidmətimizdən tam yararlanmaq üçün aşağıdan paket seçin.</div>
    </div>
</div>
@endif


{{-- ═══════════════════════════════════════════════
     PACKAGES
═══════════════════════════════════════════════ --}}
<div id="packages" class="mb-2 d-flex align-items-center justify-content-between">
    <h5 class="fw-semibold mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>Paketlər</h5>
    <span class="text-muted small">İllik paketin qiyməti aylıq qiymətdən 15% azdır</span>
</div>

<div class="row g-3 mb-4">
    @forelse($packages as $pkg)
    @php
        $annualPrice   = round($pkg->price * 12 * 0.85, 2);
        $annualSaving  = round($pkg->price * 12 - $annualPrice, 2);
        $isCurrent     = $current && $current->package_id === $pkg->id;
    @endphp
    <div class="col-md-6 col-xl-4">
        <div class="plan-card h-100 p-4 {{ $isCurrent ? 'featured' : '' }}">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0">{{ $pkg->name }}</h6>
                <div class="d-flex gap-1">
                    @if($isCurrent)
                        <span class="plan-badge bg-primary text-white">Mövcud</span>
                    @endif
                    <span class="plan-badge bg-light text-muted">{{ $pkg->duration_days }} gün</span>
                </div>
            </div>

            {{-- Monthly price --}}
            <div class="mb-1">
                <span class="fw-bold fs-4 text-dark">{{ number_format($pkg->price, 2) }} ₼</span>
                <span class="text-muted small"> / ay</span>
            </div>
            {{-- Annual price --}}
            <div class="mb-3 small text-muted">
                İllik: <span class="fw-semibold text-success">{{ number_format($annualPrice, 2) }} ₼</span>
                <span class="text-success">({{ number_format($annualSaving, 2) }} ₼ qənaət)</span>
            </div>

            {{-- Features --}}
            <ul class="list-unstyled mb-4">
                <li class="mb-2 small">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Müştəri:
                    <strong>{{ $pkg->patient_limit ?? '∞ Limitsiz' }}</strong>
                </li>
                <li class="mb-2 small">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    SMS:
                    <strong>{{ $pkg->sms_limit ?? '∞ Limitsiz' }}</strong>
                </li>
                <li class="small">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Müddət: <strong>{{ $pkg->duration_days }} gün / ödəniş</strong>
                </li>
            </ul>

            {{-- CTAs --}}
            <div class="d-grid gap-2">
                <a href="{{ route('panel.subscription.checkout', ['package' => $pkg->id, 'period' => 'monthly']) }}"
                   class="btn {{ $isCurrent ? 'btn-outline-primary' : 'btn-primary' }} btn-sm">
                    <i class="bi bi-calendar-month me-1"></i>Aylıq — {{ number_format($pkg->price, 2) }} ₼
                </a>
                <a href="{{ route('panel.subscription.checkout', ['package' => $pkg->id, 'period' => 'annual']) }}"
                   class="btn {{ $isCurrent ? 'btn-outline-success' : 'btn-success' }} btn-sm">
                    <i class="bi bi-calendar-year me-1"></i>İllik — {{ number_format($annualPrice, 2) }} ₼
                    <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">-15%</span>
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>Aktiv paket mövcud deyil.</div>
    </div>
    @endforelse
</div>


{{-- ═══════════════════════════════════════════════
     HISTORY
═══════════════════════════════════════════════ --}}
@if($history->count())
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2 text-secondary"></i>Abunəlik Tarixçəsi</h6>
    </div>
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
                @foreach($history as $sub)
                @php
                    $expired  = $sub->expires_at->isPast();
                    $isActive = $sub->is_active && !$expired;
                @endphp
                <tr>
                    <td class="fw-medium">{{ $sub->package->name }}</td>
                    <td class="text-muted small">{{ $sub->starts_at->format('d.m.Y') }}</td>
                    <td class="text-muted small">{{ $sub->expires_at->format('d.m.Y') }}</td>
                    <td class="text-muted small">
                        {{ $sub->patients_used }} / {{ $sub->package->patient_limit ?? '∞' }}
                    </td>
                    <td class="text-muted small">
                        {{ $sub->sms_used }} / {{ $sub->package->sms_limit ?? '∞' }}
                    </td>
                    <td>
                        @if($isActive)
                            <span class="badge bg-success">Aktiv</span>
                        @elseif($expired)
                            <span class="badge bg-secondary">Bitib</span>
                        @else
                            <span class="badge bg-danger">Deaktiv</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
