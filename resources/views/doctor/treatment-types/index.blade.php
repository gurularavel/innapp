@extends('layouts.doctor')

@section('title', 'Xidmət Növləri')
@section('page-title', 'Xidmət Növləri')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Xidmət Növləri</h6>
        <a href="{{ route('panel.treatment-types.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Yeni Növ
        </a>
    </div>
    <div class="card-body p-0">
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
