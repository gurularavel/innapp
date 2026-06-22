@extends('layouts.promoter')

@section('title', 'Müştərilər / Komissiya')
@section('page-title', 'Müştərilər / Komissiya')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold">Cəlb olunan müştərilər və komissiyalar</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tarix</th>
                        <th>Müştəri</th>
                        <th>Kod</th>
                        <th>Paket</th>
                        <th>Endirim</th>
                        <th>Komissiya</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($redemptions as $r)
                    <tr>
                        <td>{{ $r->created_at->format('d.m.Y') }}</td>
                        <td class="fw-medium">{{ $r->customer?->full_name ?? '—' }}</td>
                        <td>{{ $r->promoCode?->code ?? '—' }}</td>
                        <td>{{ $r->payment?->package?->name ?? '—' }}</td>
                        <td>{{ number_format($r->discount_applied, 2) }} ₼</td>
                        <td class="fw-medium">{{ number_format($r->commission_amount, 2) }} ₼</td>
                        <td>@include('promoter._status_badge', ['status' => $r->status])</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-people fs-3 d-block mb-2"></i>Hələ cəlb olunan müştəri yoxdur</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($redemptions->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">{{ $redemptions->firstItem() }}–{{ $redemptions->lastItem() }} / {{ $redemptions->total() }} nəticə</div>
        {{ $redemptions->links() }}
    </div>
    @endif
</div>
@endsection
