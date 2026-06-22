@extends('layouts.admin')

@section('title', 'Promotorlar')
@section('page-title', 'Promotorlar')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Promotorlar Siyahısı</h6>
        <a href="{{ route('admin.promoters.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Yeni Promotor
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Ad Soyad</th>
                        <th>E-poçt</th>
                        <th>Telefon</th>
                        <th>Promo Kodlar</th>
                        <th>Status</th>
                        <th class="text-end">Əməliyyatlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promoters as $promoter)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td class="fw-medium">{{ $promoter->full_name }}</td>
                        <td>{{ $promoter->email }}</td>
                        <td>{{ $promoter->phone ?? '—' }}</td>
                        <td><span class="badge bg-info">{{ $promoter->promo_codes_count }}</span></td>
                        <td>
                            @if($promoter->is_active)
                                <span class="badge bg-success">Aktiv</span>
                            @else
                                <span class="badge bg-danger">Deaktiv</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('admin.promoters.show', $promoter) }}" class="btn btn-sm btn-outline-secondary" title="Hesabat">
                                    <i class="bi bi-bar-chart"></i>
                                </a>
                                <a href="{{ route('admin.promoters.edit', $promoter) }}" class="btn btn-sm btn-outline-primary" title="Düzəlt">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.promoters.destroy', $promoter) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Silmək istədiyinizdən əminsiniz?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-person-badge fs-3 d-block mb-2"></i>
                            Promotor tapılmadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($promoters->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            {{ $promoters->firstItem() }}–{{ $promoters->lastItem() }} / {{ $promoters->total() }} nəticə
        </div>
        {{ $promoters->links() }}
    </div>
    @endif
</div>
@endsection
