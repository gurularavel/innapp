@extends('layouts.doctor')

@section('title', 'Xidmət Növləri')
@section('page-title', 'Xidmət Növləri')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <h6 class="mb-0 fw-semibold flex-shrink-0">Xidmət Növləri</h6>
            <div class="flex-grow-1">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="search" id="treatment-search"
                           class="form-control border-start-0 ps-0"
                           placeholder="Axtar...">
                </div>
            </div>
            <a href="{{ route('panel.treatment-types.create') }}" class="btn btn-primary btn-sm flex-shrink-0">
                <i class="bi bi-plus-lg"></i><span class="d-none d-md-inline ms-1">Yeni Növ</span>
            </a>
        </div>
    </div>

    {{-- Mobile card view --}}
    <div class="d-md-none">
        @forelse($treatmentTypes as $type)
        <div class="px-3 py-3 border-bottom d-flex align-items-center gap-3">
            <span class="d-inline-block rounded-circle border flex-shrink-0"
                  style="width:28px;height:28px;background-color:{{ $type->color ?? '#3788d8' }};"></span>
            <div class="flex-grow-1 min-w-0">
                <div class="fw-medium">{{ $type->name }}</div>
                <div class="text-muted small">
                    {{ $type->duration_minutes ?? 30 }} dəq
                    @if($type->price !== null)
                        · {{ number_format($type->price, 2) }} ₼
                    @endif
                </div>
            </div>
            <div class="d-flex gap-1 flex-shrink-0">
                <a href="{{ route('panel.treatment-types.edit', $type) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i>
                </a>
                <form method="POST" action="{{ route('panel.treatment-types.destroy', $type) }}" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"
                            onclick="return confirm('Silmək istədiyinizdən əminsiniz?')">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <i class="bi bi-clipboard-x fs-3 d-block mb-2"></i>Xidmət növü tapılmadı
        </div>
        @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="card-body p-0 d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Rəng</th>
                        <th>Ad</th>
                        <th>Qiymət</th>
                        <th>Müddət (dəq)</th>
                        <th class="text-end">Əməliyyatlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($treatmentTypes as $type)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td>
                            <span class="d-inline-block rounded-circle border"
                                  style="width:24px;height:24px;background-color:{{ $type->color ?? '#3788d8' }};"
                                  title="{{ $type->color ?? '#3788d8' }}"></span>
                        </td>
                        <td class="fw-medium">{{ $type->name }}</td>
                        <td>{{ $type->price !== null ? number_format($type->price, 2) . ' ₼' : '—' }}</td>
                        <td>{{ $type->duration_minutes ?? 30 }} dəq</td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('panel.treatment-types.edit', $type) }}" class="btn btn-sm btn-outline-primary" title="Düzəlt">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('panel.treatment-types.destroy', $type) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Silmək istədiyinizdən əminsiniz?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-clipboard-x fs-3 d-block mb-2"></i>
                            Xidmət növü tapılmadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('treatment-search').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();

    // Mobile cards
    document.querySelectorAll('.d-md-none > .border-bottom').forEach(function (card) {
        const name = card.querySelector('.fw-medium')?.textContent.toLowerCase() || '';
        card.style.display = (!q || name.includes(q)) ? '' : 'none';
    });

    // Desktop table rows
    document.querySelectorAll('.table tbody tr').forEach(function (row) {
        if (row.querySelector('td[colspan]')) return; // empty state row
        const name = row.querySelector('.fw-medium')?.textContent.toLowerCase() || '';
        row.style.display = (!q || name.includes(q)) ? '' : 'none';
    });
});
</script>
@endpush
