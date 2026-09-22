@extends('layouts.admin')

@section('title', 'Abunəliklər')
@section('page-title', 'Abunəliklər')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Abunəliklər Siyahısı</h6>
        <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Yeni Abunəlik
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>İstifadəçi</th>
                        <th>Müəssisə</th>
                        <th>Paket</th>
                        <th>Başlanğıc</th>
                        <th>Bitmə</th>
                        <th>Müştəri</th>
                        <th>SMS</th>
                        <th>Status</th>
                        <th class="text-end">Əməliyyatlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                    <tr>
                        <td class="text-muted small">{{ $subscriptions->firstItem() + $loop->index }}</td>
                        <td>
                            @if($subscription->doctor)
                                <a href="{{ route('admin.users.show', $subscription->doctor) }}" class="text-decoration-none fw-medium">
                                    {{ $subscription->doctor->full_name }}
                                </a>
                                <div class="text-muted small">{{ $subscription->doctor->email }}</div>
                            @else
                                {{-- doctor_id is nullOnDelete: the staff member is gone, the clinic's record stays. --}}
                                <span class="fw-medium text-muted">Silinmiş istifadəçi</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $subscription->clinic?->name ?? '—' }}</td>
                        <td class="fw-medium">{{ $subscription->package->name }}</td>
                        <td class="text-muted small">{{ $subscription->starts_at->format('d.m.Y') }}</td>
                        <td>
                            <span class="{{ $subscription->expires_at->isPast() ? 'text-danger' : 'text-muted' }} small">
                                {{ $subscription->expires_at->format('d.m.Y') }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            {{ $subscription->patients_used }}/{{ $subscription->package->patient_limit ?? '∞' }}
                        </td>
                        <td class="text-muted small">
                            {{ $subscription->used_seats }}/{{ $subscription->seats }}
                        </td>
                        <td>
                            @if($subscription->is_active && $subscription->expires_at->isFuture())
                                <span class="badge bg-success">Aktiv</span>
                            @elseif($subscription->expires_at->isPast())
                                <span class="badge bg-secondary">Bitib</span>
                            @else
                                <span class="badge bg-danger">Deaktiv</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.subscriptions.edit', $subscription) }}"
                                   class="btn btn-outline-primary" title="Redaktə et">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                                        data-bs-toggle="dropdown" aria-expanded="false" title="Uzat">
                                    <i class="bi bi-calendar-plus"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @foreach(['+1 ay' => 30, '+3 ay' => 90, '+6 ay' => 180, '+1 il' => 365] as $label => $days)
                                    <li>
                                        <form method="POST" action="{{ route('admin.subscriptions.extend', $subscription) }}">
                                            @csrf
                                            <input type="hidden" name="days" value="{{ $days }}">
                                            <button type="submit" class="dropdown-item">{{ $label }}</button>
                                        </form>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            <form method="POST" action="{{ route('admin.subscriptions.destroy', $subscription) }}" class="d-inline ms-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" data-confirm="Silmək istədiyinizdən əminsiniz?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="bi bi-credit-card fs-3 d-block mb-2"></i>
                            Abunəlik tapılmadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($subscriptions->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            {{ $subscriptions->firstItem() }}–{{ $subscriptions->lastItem() }} / {{ $subscriptions->total() }} nəticə
        </div>
        {{ $subscriptions->links() }}
    </div>
    @endif
</div>
@endsection
