@extends('layouts.promoter')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fffbeb;color:#d97706"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-label">Gözləyən</div>
                    <div class="stat-value">{{ number_format($balances['pending'], 2) }} ₼</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#ecfdf5;color:#059669"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="stat-label">Çıxarıla bilən</div>
                    <div class="stat-value">{{ number_format($balances['available'], 2) }} ₼</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#eff6ff;color:#2563eb"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="stat-label">Ödənilmiş</div>
                    <div class="stat-value">{{ number_format($balances['paid'], 2) }} ₼</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#f5f3ff;color:#7c3aed"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-label">Cəlb olunan</div>
                    <div class="stat-value">{{ $totalCustomers }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Promo Kodlarım</h6>
                <a href="{{ route('promoter.payouts.index') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-cash-coin me-1"></i>Çıxarış
                </a>
            </div>
            <div class="card-body">
                @forelse($codes as $code)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <span class="badge bg-info">{{ $code->code }}</span>
                            <span class="text-muted small ms-1">{{ $code->redemptions_count }} müştəri</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary copy-link"
                                data-link="{{ route('register') }}?promo={{ $code->code }}" title="Qeydiyyat linkini kopyala">
                            <i class="bi bi-link-45deg"></i> Link
                        </button>
                    </div>
                @empty
                    <p class="text-muted text-center mb-0 py-3">Sizə hələ promo kod təyin edilməyib.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Son Komissiyalar</h6>
                <a href="{{ route('promoter.redemptions') }}" class="small text-decoration-none">Hamısı</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Tarix</th><th>Paket</th><th>Komissiya</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse($recent as $r)
                            <tr>
                                <td>{{ $r->created_at->format('d.m.Y') }}</td>
                                <td>{{ $r->payment?->package?->name ?? '—' }}</td>
                                <td class="fw-medium">{{ number_format($r->commission_amount, 2) }} ₼</td>
                                <td>@include('promoter._status_badge', ['status' => $r->status])</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Hələ komissiya yoxdur</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.copy-link').forEach(function (btn) {
    btn.addEventListener('click', function () {
        navigator.clipboard.writeText(btn.dataset.link).then(function () {
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Kopyalandı';
            setTimeout(function () { btn.innerHTML = original; }, 1500);
        });
    });
});
</script>
@endpush
