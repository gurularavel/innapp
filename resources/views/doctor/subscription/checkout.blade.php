@extends('layouts.doctor')

@section('title', 'Ödəniş')
@section('page-title', 'Ödəniş')

@push('styles')
<style>
    .checkout-card { border: none; border-radius: .75rem; }
    .period-btn { border: 2px solid #dee2e6; border-radius: .6rem; cursor: pointer; transition: all .2s; padding: 1.1rem 1rem; user-select: none; }
    .period-btn:hover { border-color: #3788d8; box-shadow: 0 2px 8px rgba(55,136,216,.12); }
    .period-btn.selected        { border-color: #3788d8; background: #f0f7ff; }
    .period-btn.selected-annual { border-color: #198754; background: #f0faf4; }
    .summary-divider { border-top: 1px dashed #dee2e6; }
</style>
@endpush

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        <a href="{{ route('doctor.subscription.index') }}"
           class="text-decoration-none text-muted small d-inline-flex align-items-center mb-3">
            <i class="bi bi-arrow-left me-1"></i>Abunəliyə qayıt
        </a>

        <div class="row g-4">

            {{-- ═══ LEFT: Period + Pay button ═══ --}}
            <div class="col-md-6">
                <div class="card checkout-card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-calendar2-check me-2 text-primary"></i>Ödəniş Dövrü Seçin
                        </h6>
                    </div>
                    <div class="card-body d-flex flex-column">

                        <form method="POST" action="{{ route('doctor.subscription.pay', $package) }}" id="checkout-form">
                            @csrf
                            <input type="hidden" name="period" id="period-input"
                                   value="{{ request('period', 'monthly') }}">

                            @php
                                $annualPrice  = round($package->price * 12 * 0.85, 2);
                                $annualSaving = round($package->price * 12 - $annualPrice, 2);
                            @endphp

                            <div class="d-flex flex-column gap-3 mb-4">
                                {{-- Monthly --}}
                                <div class="period-btn {{ request('period','monthly') === 'monthly' ? 'selected' : '' }}"
                                     data-period="monthly">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="fw-semibold">Aylıq</div>
                                            <div class="text-muted small">{{ $package->duration_days }} gün</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-primary fs-5">{{ number_format($package->price, 2) }} ₼</div>
                                            <div class="text-muted" style="font-size:.72rem">/ ay</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Annual --}}
                                <div class="period-btn {{ request('period','monthly') === 'annual' ? 'selected-annual' : '' }}"
                                     data-period="annual">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-semibold">İllik</span>
                                                <span class="badge bg-warning text-dark" style="font-size:.65rem">-15%</span>
                                            </div>
                                            <div class="text-muted small">{{ $package->duration_days * 12 }} gün</div>
                                            <div class="text-success small">{{ number_format($annualSaving, 2) }} ₼ qənaət</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success fs-5">{{ number_format($annualPrice, 2) }} ₼</div>
                                            <div class="text-muted" style="font-size:.72rem">/ il</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto">
                                <button type="submit" class="btn btn-success w-100 py-2 fw-semibold fs-6" id="pay-btn">
                                    <i class="bi bi-lock-fill me-2"></i>
                                    <span id="pay-label">Ödənişə Keç</span>
                                </button>
                                <div class="text-center mt-2 text-muted" style="font-size:.73rem">
                                    <i class="bi bi-shield-lock me-1"></i>Bankla inteqrasiya tezliklə əlavə olunacaq
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            {{-- ═══ RIGHT: Order summary ═══ --}}
            <div class="col-md-6">
                <div class="card checkout-card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-receipt me-2 text-secondary"></i>Sifariş Xülasəsi
                        </h6>
                    </div>
                    <div class="card-body">
                        {{-- Package info --}}
                        <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                 style="width:40px;height:40px;flex-shrink:0">
                                <i class="bi bi-box-seam text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $package->name }}</div>
                                <div class="text-muted small">
                                    {{ $package->patient_limit ?? '∞' }} müştəri ·
                                    {{ $package->sms_limit ?? '∞' }} SMS
                                </div>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted">Müddət</span>
                            <span id="summary-duration" class="fw-medium">—</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted">Başlanğıc tarixi</span>
                            @if($current && $current->expires_at->isFuture())
                                <span class="fw-medium">{{ $current->expires_at->addDay()->format('d.m.Y') }}</span>
                            @else
                                <span class="fw-medium">Bu gün, {{ now()->format('d.m.Y') }}</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between small mb-0">
                            <span class="text-muted">Bitmə tarixi</span>
                            <span id="summary-expires" class="fw-medium">—</span>
                        </div>

                        {{-- Total --}}
                        <div class="summary-divider mt-3 pt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">Cəmi məbləğ</span>
                                <span id="summary-total" class="fw-bold fs-4 text-success">—</span>
                            </div>
                        </div>

                        @if($current && $current->expires_at->isFuture())
                        <div class="alert alert-info py-2 small mt-3 mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Mövcud abunəliyiniz <strong>{{ $current->expires_at->format('d.m.Y') }}</strong> tarixinə kimi davam edir. Yeni abunəlik ondan sonra başlayacaq.
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const pkg = {
        price:        {{ $package->price }},
        durationDays: {{ $package->duration_days }},
        annualPrice:  {{ round($package->price * 12 * 0.85, 2) }},
    };

    @if($current && $current->expires_at->isFuture())
    const baseStart = new Date('{{ $current->expires_at->copy()->addDay()->toDateString() }}');
    @else
    const baseStart = new Date();
    @endif

    let currentPeriod = '{{ request('period', 'monthly') }}';

    function formatDate(d) {
        return String(d.getDate()).padStart(2, '0') + '.'
             + String(d.getMonth() + 1).padStart(2, '0') + '.'
             + d.getFullYear();
    }

    function updateSummary(period) {
        const isAnnual = period === 'annual';
        const price    = isAnnual ? pkg.annualPrice : pkg.price;
        const days     = isAnnual ? pkg.durationDays * 12 : pkg.durationDays;
        const durLabel = isAnnual ? '1 il (' + days + ' gün)' : '1 ay (' + days + ' gün)';

        const expires = new Date(baseStart);
        expires.setDate(expires.getDate() + days);

        document.getElementById('summary-duration').textContent = durLabel;
        document.getElementById('summary-expires').textContent  = formatDate(expires);
        document.getElementById('summary-total').textContent    = price.toFixed(2) + ' ₼';
        document.getElementById('pay-label').textContent        = 'Ödənişə Keç — ' + price.toFixed(2) + ' ₼';
        document.getElementById('period-input').value           = period;
    }

    document.querySelectorAll('.period-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            currentPeriod = this.dataset.period;
            document.querySelectorAll('.period-btn').forEach(function (b) {
                b.classList.remove('selected', 'selected-annual');
            });
            this.classList.add(currentPeriod === 'annual' ? 'selected-annual' : 'selected');
            updateSummary(currentPeriod);
        });
    });

    updateSummary(currentPeriod);

    document.getElementById('checkout-form').addEventListener('submit', function () {
        const btn = document.getElementById('pay-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Emal olunur...';
    });
})();
</script>
@endpush
