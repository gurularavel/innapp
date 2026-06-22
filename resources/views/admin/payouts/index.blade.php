@extends('layouts.admin')

@section('title', 'Çıxarışlar')
@section('page-title', 'Çıxarış Tələbləri')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold">Promotor Çıxarış Tələbləri</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tarix</th>
                        <th>Promotor</th>
                        <th>Məbləğ</th>
                        <th>Rekvizit</th>
                        <th>Qeyd</th>
                        <th>Status</th>
                        <th class="text-end">Əməliyyatlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payouts as $payout)
                    <tr>
                        <td>{{ optional($payout->requested_at)->format('d.m.Y H:i') }}</td>
                        <td class="fw-medium">{{ $payout->promoter?->full_name ?? '—' }}</td>
                        <td class="fw-medium">{{ number_format($payout->amount, 2) }} ₼</td>
                        <td>{{ $payout->method ?? '—' }}</td>
                        <td class="small text-muted">{{ $payout->note ?? '—' }}</td>
                        <td>
                            @if($payout->status === 'paid')
                                <span class="badge bg-success">Ödənilib</span>
                                <div class="small text-muted mt-1">{{ optional($payout->paid_at)->format('d.m.Y') }}</div>
                            @elseif($payout->status === 'rejected')
                                <span class="badge bg-danger">Rədd edilib</span>
                            @else
                                <span class="badge bg-warning">Gözləyir</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($payout->status === 'requested')
                            <div class="d-flex justify-content-end gap-1">
                                <form method="POST" action="{{ route('admin.payouts.paid', $payout) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success"
                                            onclick="return confirm('{{ number_format($payout->amount, 2) }} ₼ ödənildi olaraq işarələnsin?')">
                                        <i class="bi bi-check-lg me-1"></i>Ödədim
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.payouts.reject', $payout) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Tələb rədd edilsin?')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-cash-stack fs-3 d-block mb-2"></i>
                            Çıxarış tələbi yoxdur
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payouts->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            {{ $payouts->firstItem() }}–{{ $payouts->lastItem() }} / {{ $payouts->total() }} nəticə
        </div>
        {{ $payouts->links() }}
    </div>
    @endif
</div>
@endsection
