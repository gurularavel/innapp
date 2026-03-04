@extends('layouts.doctor')

@section('title', 'Randevular')
@section('page-title', 'Randevular')

@section('content')
{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('panel.appointments.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="status" class="form-label fw-medium small">Status</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="">— Hamısı —</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Gözləyir</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Təsdiqləndi</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Ləğv edildi</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date" class="form-label fw-medium small">Tarix</label>
                <input type="date" class="form-control form-control-sm" id="date" name="date"
                       value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <label for="patient_id" class="form-label fw-medium small">Müştəri</label>
                <select class="form-select form-select-sm" id="patient_id" name="patient_id">
                    <option value="">— Hamısı —</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                            {{ $patient->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i>Filtrele
                </button>
                <a href="{{ route('panel.appointments.index') }}" class="btn btn-outline-secondary btn-sm" title="Sıfırla">
                    <i class="bi bi-x-lg"></i>
                </a>
                <a href="{{ route('panel.appointments.create') }}" class="btn btn-success btn-sm ms-auto">
                    <i class="bi bi-plus-lg me-1"></i>Yeni
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Randevular</h6>
        <span class="badge bg-secondary">{{ $appointments->total() }} nəticə</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Müştəri</th>
                        <th>Xidmət Növü</th>
                        <th>Tarix / Saat</th>
                        <th>Müddət</th>
                        <th>Status</th>
                        <th class="text-end">Əməliyyatlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $apt)
                    <tr>
                        <td class="text-muted small">{{ $appointments->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('panel.patients.show', $apt->patient) }}" class="text-decoration-none fw-medium">
                                {{ $apt->patient->full_name }}
                            </a>
                        </td>
                        <td>
                            @if($apt->treatmentType)
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="rounded-circle d-inline-block"
                                          style="width:10px;height:10px;background-color:{{ $apt->treatmentType->color ?? '#3788d8' }};"></span>
                                    {{ $apt->treatmentType->name }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-medium small">{{ $apt->scheduled_at->format('d.m.Y') }}</div>
                            <div class="text-muted" style="font-size:.75rem;">{{ $apt->scheduled_at->format('H:i') }}</div>
                        </td>
                        <td class="text-muted small">{{ $apt->duration_minutes }} dəq</td>
                        <td>
                            @php
                                $badges = ['pending'=>'warning','confirmed'=>'info','completed'=>'success','cancelled'=>'danger'];
                                $labels = ['pending'=>'Gözləyir','confirmed'=>'Təsdiqləndi','completed'=>'Tamamlandı','cancelled'=>'Ləğv edildi'];
                            @endphp
                            <span class="badge bg-{{ $badges[$apt->status] ?? 'secondary' }}">
                                {{ $labels[$apt->status] ?? $apt->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('panel.appointments.show', $apt) }}" class="btn btn-sm btn-outline-info" title="Bax">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('panel.appointments.edit', $apt) }}" class="btn btn-sm btn-outline-primary" title="Düzəlt">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('panel.appointments.destroy', $apt) }}" class="d-inline">
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
                            <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                            Randevu tapılmadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($appointments->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            {{ $appointments->firstItem() }}–{{ $appointments->lastItem() }} / {{ $appointments->total() }} nəticə
        </div>
        {{ $appointments->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
