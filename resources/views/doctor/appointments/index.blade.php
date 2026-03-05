@extends('layouts.doctor')

@section('title', 'Randevular')
@section('page-title', 'Randevular')

@section('content')
@php
    $badges = ['pending'=>'warning','confirmed'=>'info','completed'=>'success','cancelled'=>'danger'];
    $labels = ['pending'=>'Gözləyir','confirmed'=>'Təsdiqləndi','completed'=>'Tamamlandı','cancelled'=>'Ləğv edildi'];
@endphp

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('panel.appointments.index') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label for="status" class="form-label fw-medium small">Status</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="">— Hamısı —</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Gözləyir</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Təsdiqləndi</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Ləğv edildi</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label for="date" class="form-label fw-medium small">Tarix</label>
                <input type="date" class="form-control form-control-sm" id="date" name="date"
                       value="{{ request('date') }}">
            </div>
            <div class="col-12 col-md-3">
                <label for="filter_patient_search" class="form-label fw-medium small">Müştəri</label>
                <input type="hidden" name="patient_id" id="filter_patient_id" value="{{ request('patient_id') }}">
                <div class="position-relative">
                    <input type="text" id="filter_patient_search"
                           class="form-control form-control-sm"
                           placeholder="Ad, soyad və ya telefon..."
                           autocomplete="off"
                           value="{{ $selectedPatient?->full_name ?? '' }}">
                    <div id="filter_patient_dropdown"
                         class="list-group position-absolute w-100 shadow"
                         style="z-index:1050;display:none;max-height:220px;overflow-y:auto;"></div>
                </div>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2 align-items-end">
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

{{-- List --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Randevular</h6>
        <span class="badge bg-secondary">{{ $appointments->total() }} nəticə</span>
    </div>

    {{-- Mobile card view --}}
    <div class="d-md-none">
        @forelse($appointments as $apt)
        <div class="px-3 py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <a href="{{ route('panel.patients.show', $apt->patient) }}" class="fw-semibold text-decoration-none">
                    {{ $apt->patient->full_name }}
                </a>
                <span class="badge bg-{{ $badges[$apt->status] ?? 'secondary' }} ms-2 flex-shrink-0">
                    {{ $labels[$apt->status] ?? $apt->status }}
                </span>
            </div>
            <div class="text-muted small mb-1">
                <i class="bi bi-clock me-1"></i>{{ $apt->scheduled_at->format('d.m.Y H:i') }}
                · {{ $apt->duration_minutes }} dəq
            </div>
            @if($apt->treatmentType)
            <div class="text-muted small mb-2">
                <span class="rounded-circle d-inline-block me-1"
                      style="width:8px;height:8px;background:{{ $apt->treatmentType->color ?? '#3788d8' }};"></span>
                {{ $apt->treatmentType->name }}
            </div>
            @endif
            <div class="d-flex gap-1 mt-2">
                <a href="{{ route('panel.appointments.show', $apt) }}" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('panel.appointments.edit', $apt) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i>
                </a>
                <form method="POST" action="{{ route('panel.appointments.destroy', $apt) }}" class="d-inline">
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
            <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>Randevu tapılmadı
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
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>Randevu tapılmadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($appointments->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            {{ $appointments->firstItem() }}–{{ $appointments->lastItem() }} / {{ $appointments->total() }} nəticə
        </div>
        {{ $appointments->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    const searchInput = document.getElementById('filter_patient_search');
    const hiddenInput = document.getElementById('filter_patient_id');
    const dropdown    = document.getElementById('filter_patient_dropdown');
    const searchUrl   = '{{ route('panel.patients.search') }}';
    let acTimer = null;

    searchInput.addEventListener('keyup', function () {
        clearTimeout(acTimer);
        const q = this.value.trim();
        if (q.length < 2) { dropdown.style.display = 'none'; return; }

        acTimer = setTimeout(function () {
            fetch(`${searchUrl}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(patients => {
                    if (!patients.length) { dropdown.style.display = 'none'; return; }
                    dropdown.innerHTML = patients.map(p => {
                        const info = p.phone ? `<small class="text-muted ms-2">${p.phone}</small>` : '';
                        return `<button type="button"
                                    class="list-group-item list-group-item-action py-2 px-3 filter-patient-item"
                                    data-id="${p.id}" data-name="${p.name} ${p.surname}">
                                    <span class="fw-medium">${p.name} ${p.surname}</span>${info}
                                </button>`;
                    }).join('');
                    dropdown.style.display = 'block';
                })
                .catch(() => {});
        }, 300);
    });

    document.addEventListener('click', function (e) {
        const item = e.target.closest('.filter-patient-item');
        if (item) {
            hiddenInput.value     = item.dataset.id;
            searchInput.value     = item.dataset.name;
            dropdown.style.display = 'none';
            return;
        }
        if (!e.target.closest('#filter_patient_search') && !e.target.closest('#filter_patient_dropdown')) {
            dropdown.style.display = 'none';
        }
    });

    searchInput.addEventListener('input', function () {
        if (!this.value.trim()) {
            hiddenInput.value = '';
        }
    });
})();
</script>
@endpush
