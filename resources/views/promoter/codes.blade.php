@extends('layouts.promoter')

@section('title', 'Promo Kodlarım')
@section('page-title', 'Promo Kodlarım')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold">Sizə təyin olunmuş promo kodlar</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kod</th>
                        <th>Endirim</th>
                        <th>Komissiya</th>
                        <th>İstifadə</th>
                        <th>Status</th>
                        <th class="text-end">Qeydiyyat Linki</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($codes as $code)
                    <tr>
                        <td><span class="badge bg-info">{{ $code->code }}</span></td>
                        <td>{{ $code->discount_type === 'percent' ? $code->discount_value . '%' : number_format($code->discount_value, 2) . ' ₼' }}</td>
                        <td class="fw-medium">{{ $code->commission_type === 'percent' ? $code->commission_value . '%' : number_format($code->commission_value, 2) . ' ₼' }}</td>
                        <td>{{ $code->redemptions_count }}{{ $code->max_uses ? ' / ' . $code->max_uses : '' }}</td>
                        <td>
                            @if($code->isUsable())<span class="badge bg-success">Aktiv</span>
                            @elseif(!$code->is_active)<span class="badge bg-danger">Deaktiv</span>
                            @else<span class="badge bg-warning">Bitib</span>@endif
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary copy-link"
                                    data-link="{{ route('register') }}?promo={{ $code->code }}">
                                <i class="bi bi-link-45deg"></i> Linki kopyala
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-ticket-perforated fs-3 d-block mb-2"></i>Promo kod təyin edilməyib</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($codes->hasPages())
    <div class="card-footer bg-white border-top">{{ $codes->links() }}</div>
    @endif
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
