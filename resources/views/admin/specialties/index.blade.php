@extends('layouts.admin')

@section('title', 'İxtisaslar')
@section('page-title', 'İxtisaslar')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">İxtisaslar Siyahısı</h6>
        <a href="{{ route('admin.specialties.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Yeni İxtisas
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Ad</th>
                        <th>İstifadəçilər sayı</th>
                        <th>Status</th>
                        <th class="text-end">Əməliyyatlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($specialties as $specialty)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td class="fw-medium">{{ $specialty->name }}</td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                {{ $specialty->doctors_count ?? $specialty->doctors->count() }}
                            </span>
                        </td>
                        <td>
                            @if($specialty->is_active)
                                <span class="badge bg-success">Aktiv</span>
                            @else
                                <span class="badge bg-danger">Deaktiv</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('admin.specialties.edit', $specialty) }}" class="btn btn-sm btn-outline-primary" title="Düzəlt">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.specialties.destroy', $specialty) }}" class="d-inline">
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
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-bookmark-x fs-3 d-block mb-2"></i>
                            İxtisas tapılmadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($specialties) && method_exists($specialties, 'hasPages') && $specialties->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            {{ $specialties->firstItem() }}–{{ $specialties->lastItem() }} / {{ $specialties->total() }} nəticə
        </div>
        {{ $specialties->links() }}
    </div>
    @endif
</div>
@endsection
