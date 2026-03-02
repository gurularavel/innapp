@extends('layouts.admin')

@section('title', 'İstifadəçilər')
@section('page-title', 'İstifadəçilər')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">İstifadəçilər Siyahısı</h6>
        <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus me-1"></i>Yeni İstifadəçi
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Ad Soyad</th>
                        <th>Email</th>
                        <th>Telefon</th>
                        <th>İxtisas</th>
                        <th>Abunəlik</th>
                        <th>Status</th>
                        <th class="text-end">Əməliyyatlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                    <tr>
                        <td class="text-muted small">{{ $doctors->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('admin.doctors.show', $doctor) }}" class="text-decoration-none fw-medium">
                                {{ $doctor->full_name }}
                            </a>
                        </td>
                        <td class="text-muted">{{ $doctor->email }}</td>
                        <td class="text-muted">{{ $doctor->phone ?? '—' }}</td>
                        <td>{{ $doctor->specialty?->name ?? '—' }}</td>
                        <td>
                            @if($doctor->activeSubscription)
                                <span class="badge bg-success">{{ $doctor->activeSubscription->package->name }}</span>
                            @else
                                <span class="badge bg-secondary">Yoxdur</span>
                            @endif
                        </td>
                        <td>
                            @if($doctor->is_active)
                                <span class="badge bg-success">Aktiv</span>
                            @else
                                <span class="badge bg-danger">Deaktiv</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('admin.doctors.show', $doctor) }}" class="btn btn-sm btn-outline-info" title="Bax">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-sm btn-outline-primary" title="Düzəlt">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.doctors.toggle-status', $doctor) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="{{ $doctor->is_active ? 'Deaktiv et' : 'Aktiv et' }}">
                                        <i class="bi bi-{{ $doctor->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.doctors.destroy', $doctor) }}" class="d-inline">
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
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-person-x fs-3 d-block mb-2"></i>
                            İstifadəçi tapılmadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($doctors->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            {{ $doctors->firstItem() }}–{{ $doctors->lastItem() }} / {{ $doctors->total() }} nəticə
        </div>
        {{ $doctors->links() }}
    </div>
    @endif
</div>
@endsection
