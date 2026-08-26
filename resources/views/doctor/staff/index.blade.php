@extends('layouts.doctor')

@section('title', 'Əməkdaşlar')
@section('page-title', 'Əməkdaşlar')

@section('content')
@php
    $used      = $clinic->usedSeats();
    $paid      = $clinic->paidSeats();
    $remaining = $clinic->remainingSeats();
    $over      = $clinic->isOverSeatLimit();
@endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Seat summary --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap align-items-center gap-3">
        <div class="flex-grow-1">
            <div class="fw-semibold mb-1">{{ $clinic->name }}</div>
            <div class="text-muted small">
                Hər aktiv hesab bir ödənişli yerdir — mütəxəssis, resepsiyonist və sahib daxil.
            </div>
        </div>

        <div class="d-flex gap-4 text-center">
            <div>
                <div class="fs-4 fw-bold {{ $over ? 'text-danger' : '' }}">{{ $used }}</div>
                <div class="text-muted" style="font-size:.75rem;">İSTİFADƏDƏ</div>
            </div>
            <div>
                <div class="fs-4 fw-bold">{{ $paid }}</div>
                <div class="text-muted" style="font-size:.75rem;">ÖDƏNİLİB</div>
            </div>
            <div>
                <div class="fs-4 fw-bold {{ $remaining > 0 ? 'text-success' : 'text-muted' }}">{{ $remaining }}</div>
                <div class="text-muted" style="font-size:.75rem;">BOŞ YER</div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('panel.subscription.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-person-plus me-1"></i>Yer al
            </a>
            @if($remaining > 0)
                <a href="{{ route('panel.staff.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Əməkdaş əlavə et
                </a>
            @else
                <button class="btn btn-primary" disabled title="Boş yer yoxdur">
                    <i class="bi bi-plus-lg me-1"></i>Əməkdaş əlavə et
                </button>
            @endif
        </div>
    </div>

    @if($over)
        <div class="card-footer bg-danger-subtle border-0 small">
            <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>
            Aktiv hesab sayı ödənilmiş yer sayını keçir. Panelin bütün bölmələrindən istifadə üçün
            yer sayını artırın və ya artıq hesabları deaktiv edin.
        </div>
    @elseif(!$subscription)
        <div class="card-footer bg-warning-subtle border-0 small">
            <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
            Aktiv abunəliyiniz yoxdur. Əməkdaş əlavə etmək üçün əvvəlcə abunə alın.
        </div>
    @endif
</div>

{{-- Staff table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Komanda</h6>
        <span class="badge bg-secondary">{{ $staff->count() }} hesab</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ad Soyad</th>
                        <th>Rol</th>
                        <th>İxtisas / Vəzifə</th>
                        <th>Təqvim</th>
                        <th>Əlaqə</th>
                        <th>Status</th>
                        <th class="text-end">Əməliyyat</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($staff as $member)
                    <tr class="{{ $member->is_active ? '' : 'opacity-50' }}">
                        <td>
                            <span class="fw-medium">{{ $member->full_name }}</span>
                            @if($member->id === $clinic->owner_id)
                                <span class="badge bg-primary ms-1">Sahib</span>
                            @endif
                            @if($member->id === auth()->id())
                                <span class="badge bg-light text-dark border ms-1">Siz</span>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $member->role_label }}</span></td>
                        <td class="small text-muted">
                            {{ $member->specialty?->name ?? $member->job_title ?? '—' }}
                        </td>
                        <td>
                            @if($member->takes_appointments)
                                <i class="bi bi-calendar-check text-success" title="Randevu qəbul edir"></i>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ $member->email }}
                            @if($member->phone)<br>{{ $member->phone }}@endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $member->is_active ? 'success' : 'secondary' }}">
                                {{ $member->is_active ? 'Aktiv' : 'Deaktiv' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('panel.staff.edit', $member) }}" class="btn btn-outline-secondary" title="Redaktə">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                @if($member->id !== $clinic->owner_id)
                                    <form method="POST" action="{{ route('panel.staff.toggle-status', $member) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-outline-{{ $member->is_active ? 'warning' : 'success' }}"
                                                title="{{ $member->is_active ? 'Deaktiv et' : 'Aktivləşdir' }}">
                                            <i class="bi bi-{{ $member->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('panel.staff.destroy', $member) }}" class="d-inline"
                                          onsubmit="return confirm('{{ $member->full_name }} silinsin? Bu əməliyyat geri qaytarılmır.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger" title="Sil">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
