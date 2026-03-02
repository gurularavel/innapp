@extends('layouts.admin')

@section('title', 'Paketlər')
@section('page-title', 'Paketlər')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Paketlər Siyahısı</h6>
        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Yeni Paket
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Ad</th>
                        <th>Qiymət</th>
                        <th>Müştəri Limiti</th>
                        <th>SMS Limiti</th>
                        <th>Müddət (gün)</th>
                        <th>Abunəliklər</th>
                        <th>Status</th>
                        <th class="text-end">Əməliyyatlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td class="fw-medium">{{ $package->name }}</td>
                        <td>{{ number_format($package->price, 2) }} ₼</td>
                        <td>{{ $package->patient_limit ?? '∞' }}</td>
                        <td>{{ $package->sms_limit ?? '∞' }}</td>
                        <td>{{ $package->duration_days }}</td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info">
                                {{ $package->subscriptions_count ?? $package->subscriptions->count() }}
                            </span>
                        </td>
                        <td>
                            @if($package->is_active)
                                <span class="badge bg-success">Aktiv</span>
                            @else
                                <span class="badge bg-danger">Deaktiv</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-sm btn-outline-primary" title="Düzəlt">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" class="d-inline">
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
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-box-seam fs-3 d-block mb-2"></i>
                            Paket tapılmadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($packages) && method_exists($packages, 'hasPages') && $packages->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            {{ $packages->firstItem() }}–{{ $packages->lastItem() }} / {{ $packages->total() }} nəticə
        </div>
        {{ $packages->links() }}
    </div>
    @endif
</div>
@endsection
