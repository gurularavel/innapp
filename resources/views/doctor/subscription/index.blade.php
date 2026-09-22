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
    $patientPct    = $patientLimit ? min(100, round($current->patients_used / $patientLimit * 100)) : 0;
    $seatsUsed     = $current->used_seats;
    $seatsPaid     = max(1, $current->seats);
    $seatPct       = min(100, round($seatsUsed / $seatsPaid * 100));
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
                    <div class="text-muted small">
                        {{ number_format($current->total_price, 2) }} ₼ / dövr
                        <span class="text-secondary">({{ number_format($current->price_per_seat, 2) }} ₼ × {{ $current->seats }})</span>
                    </div>
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
                        <span class="fw-medium"><i class="bi bi-people me-1 text-info"></i>Əməkdaş yerləri</span>
                        <span class="{{ $current->seatsExceeded() ? 'text-danger fw-semibold' : 'text-muted' }}">
                            {{ $seatsUsed }} / {{ $seatsPaid }}
                        </span>
                    </div>
                    <div class="progress usage-bar-wrap" style="height:8px">
                        <div class="progress-bar {{ $current->seatsExceeded() ? 'bg-danger' : ($seatPct >= 90 ? 'bg-warning' : 'bg-info') }}"
                             style="width:{{ $seatPct }}%"></div>
                    </div>
                    <div class="text-muted" style="font-size:.72rem">
                        Aylıq {{ number_format($current->total_price, 2) }} ₼
                        ({{ number_format($current->price_per_seat, 2) }} ₼ × {{ $seatsPaid }})
                    </div>
                    @if($current->seatsExceeded())
                        <div class="text-danger" style="font-size:.72rem">
                            Ödənilmiş yerdən çox aktiv hesab var — yer sayını artırın.
                        </div>
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
<div id="packages" class="mb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h5 class="fw-semibold mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>Paketlər</h5>
    <span class="text-muted small">İllik paketin qiyməti aylıq qiymətdən 15% azdır</span>
</div>

{{-- Seat selector: the price is simply the seat count times the per-seat price --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap align-items-center gap-3">
        <div class="flex-grow-1">
            <label for="seat-count" class="form-label fw-medium mb-1">Neçə əməkdaş üçün?</label>
            <div class="text-muted small">
                Hər aktiv hesab bir yerdir. Hazırda klinikanızda
                <strong>{{ $usedSeats }}</strong> aktiv hesab var — bundan az seçilə bilməz.
            </div>
        </div>
        <div style="width:140px">
            <input type="number" id="seat-count" class="form-control form-control-lg text-center"
                   value="{{ $usedSeats }}" min="{{ $usedSeats }}" max="500">
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    @forelse($packages as $pkg)
    @php
        $monthlyPrice  = $pkg->priceFor($usedSeats);
        $annualPrice   = round($monthlyPrice * 12 * 0.85, 2);
        $annualSaving  = round($monthlyPrice * 12 - $annualPrice, 2);
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
                <span class="fw-bold fs-4 text-dark js-monthly"
                      data-per-seat="{{ $pkg->price_per_seat }}">{{ number_format($monthlyPrice, 2) }} ₼</span>
                <span class="text-muted small"> / ay</span>
            </div>
            <div class="mb-1 text-muted small">
                {{ number_format($pkg->price_per_seat, 2) }} ₼ × <span class="js-seat-label">{{ $usedSeats }}</span> əməkdaş
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
                    SMS və WhatsApp: <strong>∞ Limitsiz</strong>
                </li>
                <li class="mb-2 small">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Əməkdaş: <strong>{{ $pkg->max_seats ? 'maks. ' . $pkg->max_seats : '∞ Limitsiz' }}</strong>
                </li>
                <li class="small">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Müddət: <strong>{{ $pkg->duration_days }} gün / ödəniş</strong>
                </li>
            </ul>

            {{-- CTAs --}}
            <div class="d-grid gap-2">
                <a href="{{ route('panel.subscription.checkout', ['package' => $pkg->id, 'period' => 'monthly']) }}"
                   class="btn {{ $isCurrent ? 'btn-outline-primary' : 'btn-primary' }} btn-sm js-checkout"
                   data-base="{{ route('panel.subscription.checkout', ['package' => $pkg->id]) }}"
                   data-period="monthly">
                    <i class="bi bi-calendar-month me-1"></i>Aylıq — <span class="js-cta-monthly">{{ number_format($monthlyPrice, 2) }}</span> ₼
                </a>
                <a href="{{ route('panel.subscription.checkout', ['package' => $pkg->id, 'period' => 'annual']) }}"
                   class="btn {{ $isCurrent ? 'btn-outline-success' : 'btn-success' }} btn-sm js-checkout"
                   data-base="{{ route('panel.subscription.checkout', ['package' => $pkg->id]) }}"
                   data-period="annual">
                    <i class="bi bi-calendar-year me-1"></i>İllik — <span class="js-cta-annual">{{ number_format($annualPrice, 2) }}</span> ₼
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
                    <th>Əməkdaş</th>
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
                        {{ $sub->seats }} yer × {{ number_format($sub->price_per_seat, 2) }} ₼
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

@push('scripts')
<script @cspNonce>
// Seat count drives every price on this page; the server recalculates and
// clamps it again at checkout, so this is presentation only.
(function () {
    const input = document.getElementById('seat-count');
    if (!input) return;

    const fmt = n => n.toLocaleString('az-AZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function recalc() {
        const seats = Math.max(parseInt(input.min, 10) || 1, parseInt(input.value, 10) || 1);
        input.value = seats;

        document.querySelectorAll('.js-seat-label').forEach(el => el.textContent = seats);

        document.querySelectorAll('.plan-card').forEach(card => {
            const monthlyEl = card.querySelector('.js-monthly');
            if (!monthlyEl) return;

            const perSeat = parseFloat(monthlyEl.dataset.perSeat) || 0;
            const monthly = perSeat * seats;
            const annual  = Math.round(monthly * 12 * 0.85 * 100) / 100;

            monthlyEl.textContent = fmt(monthly) + ' \u20bc';
            const ctaM = card.querySelector('.js-cta-monthly');
            const ctaA = card.querySelector('.js-cta-annual');
            if (ctaM) ctaM.textContent = fmt(monthly);
            if (ctaA) ctaA.textContent = fmt(annual);

            card.querySelectorAll('.js-checkout').forEach(a => {
                a.href = a.dataset.base + '?period=' + a.dataset.period + '&seats=' + seats;
            });
        });
    }

    input.addEventListener('input', recalc);
    input.addEventListener('change', recalc);
    recalc();
})();
</script>
@endpush
