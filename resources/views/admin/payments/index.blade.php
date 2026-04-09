@extends('layouts.admin')

@section('title', 'Ödəniş Hesabatı')
@section('page-title', 'Ödəniş Hesabatı')

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-cash-stack fs-4 text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">Ümumi Gəlir</div>
                    <div class="fw-bold fs-5">{{ number_format($stats['total_paid'], 2) }} ₼</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-check-circle fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">Uğurlu Ödəniş</div>
                    <div class="fw-bold fs-5">{{ $stats['count_paid'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-clock fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small">Gözləmədə</div>
                    <div class="fw-bold fs-5">{{ $stats['count_pending'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                    <i class="bi bi-x-circle fs-4 text-danger"></i>
                </div>
                <div>
                    <div class="text-muted small">Uğursuz</div>
                    <div class="fw-bold fs-5">{{ $stats['count_failed'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-6 col-md-3">
                <label class="form-label small text-muted mb-1">İstifadəçi</label>
                <select name="doctor_id" class="form-select form-select-sm">
                    <option value="">Hamısı</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                            {{ $doctor->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Hamısı</option>
                    <option value="paid"    {{ request('status') === 'paid'    ? 'selected' : '' }}>Ödənildi</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Gözləmədə</option>
                    <option value="failed"  {{ request('status') === 'failed'  ? 'selected' : '' }}>Uğursuz</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label small text-muted mb-1">Tarixdən</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label small text-muted mb-1">Tarixə qədər</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel me-1"></i>Filtrele
                </button>
                @if(request()->hasAny(['status','doctor_id','date_from','date_to']))
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm ms-1">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Ödəniş Siyahısı</h6>
        <span class="badge bg-secondary">{{ $payments->total() }} ödəniş</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>İstifadəçi</th>
                        <th>Paket</th>
                        <th>Dövr</th>
                        <th>Məbləğ</th>
                        <th>KapitalBank Order ID</th>
                        <th>Order Password</th>
                        <th>Status</th>
                        <th>Tarix</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td class="text-muted">{{ $payments->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('admin.doctors.show', $payment->doctor) }}" class="text-decoration-none fw-medium">
                                {{ $payment->doctor->full_name }}
                            </a>
                            <div class="text-muted">{{ $payment->doctor->email }}</div>
                        </td>
                        <td class="fw-medium">{{ $payment->package->name }}</td>
                        <td>
                            @if($payment->period === 'annual')
                                <span class="badge bg-info text-dark">İllik</span>
                            @else
                                <span class="badge bg-light text-dark border">Aylıq</span>
                            @endif
                        </td>
                        <td class="fw-semibold text-success">{{ number_format($payment->amount, 2) }} ₼</td>
                        <td>
                            @if($payment->kapitalbank_order_id)
                                <span class="font-monospace text-dark">{{ $payment->kapitalbank_order_id }}</span>
                                <button class="btn btn-link btn-sm p-0 ms-1 text-muted copy-btn"
                                        data-value="{{ $payment->kapitalbank_order_id }}"
                                        title="Kopyala">
                                    <i class="bi bi-copy"></i>
                                </button>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($payment->kapitalbank_order_password)
                                <span class="font-monospace password-text" style="filter:blur(4px);cursor:pointer"
                                      onclick="this.style.filter='none'"
                                      title="Görmək üçün klikləyin">{{ $payment->kapitalbank_order_password }}</span>
                                <button class="btn btn-link btn-sm p-0 ms-1 text-muted copy-btn"
                                        data-value="{{ $payment->kapitalbank_order_password }}"
                                        title="Kopyala">
                                    <i class="bi bi-copy"></i>
                                </button>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($payment->status === 'paid')
                                <span class="badge bg-success">Ödənildi</span>
                            @elseif($payment->status === 'pending')
                                <span class="badge bg-warning text-dark">Gözləmədə</span>
                            @else
                                <span class="badge bg-danger">Uğursuz</span>
                            @endif
                        </td>
                        <td class="text-muted">
                            {{ $payment->created_at->format('d.m.Y') }}
                            <div class="text-muted" style="font-size:.75em">{{ $payment->created_at->format('H:i') }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-receipt fs-2 d-block mb-2"></i>
                            Ödəniş tapılmadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            {{ $payments->firstItem() }}–{{ $payments->lastItem() }} / {{ $payments->total() }} nəticə
        </div>
        {{ $payments->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        navigator.clipboard.writeText(btn.dataset.value);
        const icon = btn.querySelector('i');
        icon.className = 'bi bi-check-lg text-success';
        setTimeout(() => icon.className = 'bi bi-copy', 1500);
    });
});
</script>
@endpush
@endsection
