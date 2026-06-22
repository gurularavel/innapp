@extends('layouts.admin')

@section('title', 'Promotor Hesabatı')
@section('page-title', $promoter->full_name . ' — Hesabat')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.promoters.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Geri
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fffbeb;color:#d97706"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-label">Gözləyən (hold)</div>
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
                    <div class="stat-label">Ödənilmiş</div>
                    <div class="stat-value">{{ number_format($balances['paid'], 2) }} ₼</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold">Promo Kodlar</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Kod</th><th>Endirim</th><th>Komissiya</th><th>İstifadə</th></tr></thead>
                <tbody>
                    @forelse($codes as $code)
                    <tr>
                        <td><span class="badge bg-info">{{ $code->code }}</span></td>
                        <td>{{ $code->discount_type === 'percent' ? $code->discount_value . '%' : number_format($code->discount_value, 2) . ' ₼' }}</td>
                        <td>{{ $code->commission_type === 'percent' ? $code->commission_value . '%' : number_format($code->commission_value, 2) . ' ₼' }}</td>
                        <td>{{ $code->redemptions_count }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">Promo kod yoxdur</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold">Komissiyalar (son 50)</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Tarix</th><th>Müştəri</th><th>Kod</th><th>Paket</th><th>Komissiya</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($redemptions as $r)
                    <tr>
                        <td>{{ $r->created_at->format('d.m.Y') }}</td>
                        <td>{{ $r->customer?->full_name ?? '—' }}</td>
                        <td>{{ $r->promoCode?->code ?? '—' }}</td>
                        <td>{{ $r->payment?->package?->name ?? '—' }}</td>
                        <td class="fw-medium">{{ number_format($r->commission_amount, 2) }} ₼</td>
                        <td>@include('promoter._status_badge', ['status' => $r->status])</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">Komissiya yoxdur</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold">Çıxarış Tələbləri</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Tarix</th><th>Məbləğ</th><th>Rekvizit</th><th>Status</th><th>Ödəniş tarixi</th></tr>
                </thead>
                <tbody>
                    @forelse($payouts as $p)
                    <tr>
                        <td>{{ optional($p->requested_at)->format('d.m.Y') }}</td>
                        <td class="fw-medium">{{ number_format($p->amount, 2) }} ₼</td>
                        <td>{{ $p->method ?? '—' }}</td>
                        <td>
                            @if($p->status === 'paid')<span class="badge bg-success">Ödənilib</span>
                            @elseif($p->status === 'rejected')<span class="badge bg-danger">Rədd edilib</span>
                            @else<span class="badge bg-warning">Gözləyir</span>@endif
                        </td>
                        <td>{{ optional($p->paid_at)->format('d.m.Y') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Çıxarış yoxdur</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
