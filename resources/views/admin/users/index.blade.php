@extends('layouts.admin')
@section('title', 'İstifadəçilər')
@section('page-title', 'İstifadəçilər')
@section('content')

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Qeydiyyatdan keçən</div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['total'] }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('admin.users.index', ['subscription' => 'active']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Abunəliyi var</div>
                    <div class="fs-4 fw-bold text-success">{{ $stats['subscribed'] }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('admin.users.index', ['subscription' => 'none']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Ödəniş etməyib</div>
                    <div class="fs-4 fw-bold text-danger">{{ $stats['unpaid'] }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('admin.users.index', ['demo' => 'only']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Demo hesab</div>
                    <div class="fs-4 fw-bold text-warning">{{ $stats['demo'] }}</div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 fw-semibold">İstifadəçilər Siyahısı</h6>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus me-1"></i>Yeni İstifadəçi
        </a>
    </div>

    {{-- Filters --}}
    <div class="card-body border-bottom bg-light py-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small text-muted mb-1">Axtarış</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                       placeholder="Ad, e-poçt, telefon, müəssisə">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Rol</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">Hamısı</option>
                    <option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>Klinika sahibi</option>
                    <option value="doctor" {{ request('role') === 'doctor' ? 'selected' : '' }}>Mütəxəssis</option>
                    <option value="receptionist" {{ request('role') === 'receptionist' ? 'selected' : '' }}>Resepsiyonist</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Abunəlik</label>
                <select name="subscription" class="form-select form-select-sm">
                    <option value="">Hamısı</option>
                    <option value="active" {{ request('subscription') === 'active' ? 'selected' : '' }}>Var (ödənişli)</option>
                    <option value="none" {{ request('subscription') === 'none' ? 'selected' : '' }}>Yoxdur</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Hesab</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Hamısı</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktiv</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Deaktiv</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Demo</label>
                <select name="demo" class="form-select form-select-sm">
                    <option value="">Hamısı</option>
                    <option value="hide" {{ request('demo') === 'hide' ? 'selected' : '' }}>Demoları gizlət</option>
                    <option value="only" {{ request('demo') === 'only' ? 'selected' : '' }}>Yalnız demo</option>
                </select>
            </div>
            <div class="col-12 d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i>Süz
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Sıfırla</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Ad Soyad</th>
                        <th>Müəssisə</th>
                        <th>Əlaqə</th>
                        <th>Abunəlik / Ödəniş</th>
                        <th>Qeydiyyat</th>
                        <th>Status</th>
                        <th class="text-end">Əməliyyatlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                    @php $sub = $doctor->clinic?->activeSubscription; @endphp
                    <tr>
                        <td class="text-muted small">{{ $doctors->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $doctor) }}" class="text-decoration-none fw-medium">
                                {{ $doctor->full_name }}
                            </a>
                            <div class="small">
                                <span class="badge bg-light text-dark border">{{ $doctor->role_label }}</span>
                                @if($doctor->is_demo)
                                    <span class="badge bg-warning text-dark">Demo</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-muted small">{{ $doctor->clinic?->name ?? '—' }}</td>
                        <td class="text-muted small">
                            {{ $doctor->email }}
                            <div>{{ $doctor->phone ?? '—' }}</div>
                        </td>
                        <td>
                            @if($sub)
                                <span class="badge bg-success">Ödənilib</span>
                                <div class="text-muted small">
                                    {{ $sub->package?->name }} ·
                                    {{ $sub->expires_at->format('d.m.Y') }}-a kimi ·
                                    {{ $sub->seats }} yer
                                </div>
                            @else
                                <span class="badge bg-danger">Ödəniş yoxdur</span>
                                <div class="text-muted small">Aktiv abunəlik yoxdur</div>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $doctor->created_at?->format('d.m.Y') }}</td>
                        <td>
                            @if($doctor->is_active)
                                <span class="badge bg-success">Aktiv</span>
                            @else
                                <span class="badge bg-danger">Deaktiv</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('admin.subscriptions.create', ['doctor_id' => $doctor->id]) }}"
                                   class="btn btn-sm btn-outline-success" title="Abunəlik ver">
                                    <i class="bi bi-credit-card"></i>
                                </a>
                                <a href="{{ route('admin.users.show', $doctor) }}" class="btn btn-sm btn-outline-info" title="Bax">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $doctor) }}" class="btn btn-sm btn-outline-primary" title="Düzəlt">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.users.toggle-status', $doctor) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="{{ $doctor->is_active ? 'Deaktiv et' : 'Aktiv et' }}">
                                        <i class="bi bi-{{ $doctor->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $doctor) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" data-confirm="Silmək istədiyinizdən əminsiniz?">
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
