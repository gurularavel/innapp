@extends('layouts.promoter')

@section('title', 'Balans / Çıxarış')
@section('page-title', 'Balans / Çıxarış')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fffbeb;color:#d97706"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-label">Gözləyən (14 gün hold)</div>
                    <div class="stat-value">{{ number_format($balances['pending'], 2) }} ₼</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
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
    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#eff6ff;color:#2563eb"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="stat-label">Ödənilmiş (cəmi)</div>
                    <div class="stat-value">{{ number_format($balances['paid'], 2) }} ₼</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold">Çıxarış Tələbi</h6></div>
            <div class="card-body">
                @if($balances['available'] > 0)
                    <p class="text-muted small mb-3">
                        Çıxarıla bilən balansınız: <strong class="text-dark">{{ number_format($balances['available'], 2) }} ₼</strong>.
                        Tələb göndərdikdə bütün çıxarıla bilən komissiyalar daxil edilir.
                    </p>
                    <form method="POST" action="{{ route('promoter.payouts.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="method" class="form-label fw-medium">Ödəniş rekvizitləri <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('method') is-invalid @enderror"
                                   id="method" name="method" value="{{ old('method') }}"
                                   placeholder="Kart nömrəsi / hesab" required>
                            @error('method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label fw-medium">Qeyd</label>
                            <textarea class="form-control" id="note" name="note" rows="2" placeholder="(istəyə bağlı)">{{ old('note') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-cash-coin me-1"></i>Çıxarış tələbi göndər
                        </button>
                    </form>
                @else
                    <p class="text-muted text-center mb-0 py-3">
                        <i class="bi bi-info-circle d-block fs-3 mb-2"></i>
                        Hazırda çıxarıla bilən balansınız yoxdur.
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold">Çıxarış Tarixçəsi</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Tarix</th><th>Məbləğ</th><th>Status</th><th>Ödəniş tarixi</th></tr>
                        </thead>
                        <tbody>
                            @forelse($payouts as $p)
                            <tr>
                                <td>{{ optional($p->requested_at)->format('d.m.Y') }}</td>
                                <td class="fw-medium">{{ number_format($p->amount, 2) }} ₼</td>
                                <td>
                                    @if($p->status === 'paid')<span class="badge bg-success">Ödənilib</span>
                                    @elseif($p->status === 'rejected')<span class="badge bg-danger">Rədd edilib</span>
                                    @else<span class="badge bg-warning">Gözləyir</span>@endif
                                </td>
                                <td>{{ $p->paid_at ? $p->paid_at->format('d.m.Y') : '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Çıxarış tələbi yoxdur</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($payouts->hasPages())
            <div class="card-footer bg-white border-top">{{ $payouts->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
