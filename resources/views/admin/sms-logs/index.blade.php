@extends('layouts.admin')

@section('title', 'SMS Loqlar')
@section('page-title', 'SMS Loqlar')

@section('content')
{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.sms-logs.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="doctor_id" class="form-label fw-medium small">İstifadəçi</label>
                <select class="form-select form-select-sm" id="doctor_id" name="doctor_id">
                    <option value="">— Hamısı —</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                            {{ $doctor->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label fw-medium small">Status</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="">— Hamısı —</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Göndərildi</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Uğursuz</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Gözləyir</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="type" class="form-label fw-medium small">Növ</label>
                <select class="form-select form-select-sm" id="type" name="type">
                    <option value="">— Hamısı —</option>
                    <option value="appointment_reminder" {{ request('type') === 'appointment_reminder' ? 'selected' : '' }}>Randevu Xatırlatması</option>
                    <option value="appointment_confirmation" {{ request('type') === 'appointment_confirmation' ? 'selected' : '' }}>Randevu Təsdiqi</option>
                    <option value="custom" {{ request('type') === 'custom' ? 'selected' : '' }}>Fərdi</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-search me-1"></i>Filtrele
                </button>
                <a href="{{ route('admin.sms-logs.index') }}" class="btn btn-outline-secondary btn-sm" title="Sıfırla">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">SMS Loqlar</h6>
        <span class="badge bg-secondary">{{ $smsLogs->total() }} nəticə</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>İstifadəçi</th>
                        <th>Telefon</th>
                        <th>Mesaj</th>
                        <th>Növ</th>
                        <th>Status</th>
                        <th>Göndərilib</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($smsLogs as $log)
                    <tr>
                        <td class="text-muted small">{{ $smsLogs->firstItem() + $loop->index }}</td>
                        <td>
                            @if($log->doctor)
                                <a href="{{ route('admin.users.show', $log->doctor) }}" class="text-decoration-none fw-medium">
                                    {{ $log->doctor->full_name }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $log->phone }}</td>
                        <td>
                            <span title="{{ $log->message }}" style="cursor:help;">
                                {{ Str::limit($log->message, 60) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $typeLabels = [
                                    'appointment_reminder'     => ['label' => 'Xatırlatma',   'color' => 'info'],
                                    'appointment_confirmation' => ['label' => 'Təsdiq',        'color' => 'primary'],
                                    'custom'                   => ['label' => 'Fərdi',         'color' => 'secondary'],
                                ];
                                $typeData = $typeLabels[$log->type] ?? ['label' => $log->type, 'color' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $typeData['color'] }}">{{ $typeData['label'] }}</span>
                        </td>
                        <td>
                            @if($log->status === 'sent')
                                <span class="badge bg-success">Göndərildi</span>
                            @elseif($log->status === 'failed')
                                <span class="badge bg-danger">Uğursuz</span>
                            @else
                                <span class="badge bg-warning text-dark">Gözləyir</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $log->sent_at ? $log->sent_at->format('d.m.Y H:i') : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-chat-dots fs-3 d-block mb-2"></i>
                            SMS loq tapılmadı
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($smsLogs->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            {{ $smsLogs->firstItem() }}–{{ $smsLogs->lastItem() }} / {{ $smsLogs->total() }} nəticə
        </div>
        {{ $smsLogs->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
