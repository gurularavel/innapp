@extends('layouts.admin')

@section('title', 'Bayram Təqvimi')
@section('page-title', 'Bayram Təqvimi')

@section('content')
<div class="alert alert-light border small d-flex gap-2">
    <i class="bi bi-info-circle text-info mt-1"></i>
    <div>
        Buradakı tarixlər bütün klinikalara görünür. Klinika istədiyi bayramı öz panelindən söndürə və ya
        mətnini dəyişə bilər — silmək isə yalnız buradan mümkündür.
        Hər il yerini dəyişən bayramlar (Ramazan, Qurban) üçün <strong>il</strong> sahəsini doldurun; onda o tarix yalnız həmin il işləyəcək.
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Bayramlar</h6>
        <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Yeni Bayram
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:160px">Tarix</th>
                        <th>Ad</th>
                        <th>Xüsusi mətn</th>
                        <th style="width:130px">Söndürən klinika</th>
                        <th style="width:100px">Status</th>
                        <th class="text-end" style="width:120px">Əməliyyatlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holidays as $holiday)
                    <tr class="{{ $holiday->hasPassed() ? 'opacity-50' : '' }}">
                        <td>
                            <span class="fw-medium">{{ $holiday->date_label }}</span>
                            @if($holiday->isOneOff())
                                <span class="badge bg-secondary ms-1" title="Yalnız bu il">bir dəfəlik</span>
                            @else
                                <div class="text-muted small">hər il</div>
                            @endif
                        </td>
                        <td class="fw-medium">{{ $holiday->name }}</td>
                        <td class="small text-muted">
                            {{ $holiday->template ? \Illuminate\Support\Str::limit($holiday->template, 70) : '— defolt mətn —' }}
                        </td>
                        <td>
                            @if($holiday->disabled_count > 0)
                                <span class="badge bg-warning text-dark">{{ $holiday->disabled_count }} klinika</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $holiday->is_active ? 'success' : 'secondary' }}">
                                {{ $holiday->is_active ? 'Aktiv' : 'Deaktiv' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.holidays.edit', $holiday) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('{{ $holiday->name }} silinsin? Bütün klinikalarda bu bayram üzrə ayarlar da silinəcək.')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Hələ bayram əlavə edilməyib.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
