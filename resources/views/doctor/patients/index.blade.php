@extends('layouts.doctor')

@section('title', 'Müştərilər')
@section('page-title', 'Müştərilər')

@section('content')
{{-- Search + Create --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('panel.patients.index') }}" class="row g-2 align-items-end">
            <div class="col-md-8">
                <label for="search" class="form-label fw-medium small">Axtarış</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}" placeholder="Ad, soyad və ya telefon ilə axtar...">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Axtar</button>
                @if(request('search'))
                    <a href="{{ route('panel.patients.index') }}" class="btn btn-outline-secondary">Sıfırla</a>
                @endif
                <a href="{{ route('panel.patients.create') }}" class="btn btn-success ms-auto">
                    <i class="bi bi-person-plus me-1"></i>Yeni Müştəri
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Müştərilər Siyahısı</h6>
        <span class="badge bg-secondary">{{ $patients->total() }} nəticə</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Ad Soyad</th>
                        <th>Telefon</th>
                        <th>Doğum tarixi</th>
                        <th>Yaş</th>
                        <th>Cins</th>
                        <th>Randevular</th>
                        <th class="text-end">Əməliyyatlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                    <tr>
                        <td class="text-muted small">{{ $patients->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('panel.patients.show', $patient) }}" class="text-decoration-none fw-medium">
                                {{ $patient->full_name }}
                            </a>
                        </td>
                        <td class="text-muted">{{ $patient->phone ?? '—' }}</td>
                        <td class="text-muted small">
                            {{ $patient->birth_date ? $patient->birth_date->format('d.m.Y') : '—' }}
                        </td>
                        <td class="text-muted small">{{ $patient->age ?? '—' }}</td>
                        <td>
                            @php
                                $genderLabels = ['male' => 'Kişi', 'female' => 'Qadın', 'other' => 'Digər'];
                            @endphp
                            {{ $genderLabels[$patient->gender] ?? '—' }}
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                {{ $patient->appointments_count ?? $patient->appointments->count() }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('panel.patients.show', $patient) }}" class="btn btn-sm btn-outline-info" title="Bax">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('panel.patients.edit', $patient) }}" class="btn btn-sm btn-outline-primary" title="Düzəlt">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('panel.patients.destroy', $patient) }}" class="d-inline">
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
                            Müştəri tapılmadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($patients->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            {{ $patients->firstItem() }}–{{ $patients->lastItem() }} / {{ $patients->total() }} nəticə
        </div>
        {{ $patients->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
