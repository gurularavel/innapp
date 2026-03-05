@extends('layouts.doctor')

@section('title', 'Randevu Detalları')
@section('page-title', 'Randevu Detalları')

@section('content')
<div class="d-flex justify-content-end align-items-center mb-4 gap-2">
    <a href="{{ route('panel.appointments.edit', $appointment) }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil me-1"></i>Düzəlt
    </a>
    <a href="{{ route('panel.appointments.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Geri
    </a>
</div>

<div class="row g-4">
    {{-- Appointment Details --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-calendar-check me-2 text-primary"></i>Randevu Məlumatları
                </h6>
            </div>
            <div class="card-body">
                @php
                    $badges = ['pending'=>'warning','confirmed'=>'info','completed'=>'success','cancelled'=>'danger'];
                    $labels = ['pending'=>'Gözləyir','confirmed'=>'Təsdiqləndi','completed'=>'Tamamlandı','cancelled'=>'Ləğv edildi'];
                @endphp

                <div class="mb-3">
                    <div class="text-muted small">Status</div>
                    <span class="badge bg-{{ $badges[$appointment->status] ?? 'secondary' }} fs-6 mt-1">
                        {{ $labels[$appointment->status] ?? $appointment->status }}
                    </span>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Tarix / Saat</div>
                    <div class="fw-medium">{{ $appointment->scheduled_at->format('d.m.Y H:i') }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Müddət</div>
                    <div class="fw-medium">{{ $appointment->duration_minutes }} dəqiqə</div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Xidmət Növü</div>
                    <div class="fw-medium d-flex align-items-center gap-2">
                        @if($appointment->treatmentType)
                            <span class="rounded-circle d-inline-block border"
                                  style="width:14px;height:14px;background-color:{{ $appointment->treatmentType->color ?? '#3788d8' }};"></span>
                            {{ $appointment->treatmentType->name }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>

                @if($appointment->notes)
                <div class="mb-3">
                    <div class="text-muted small">Qeydlər</div>
                    <div class="fw-medium mt-1 p-2 bg-light rounded small">{{ $appointment->notes }}</div>
                </div>
                @endif

                {{-- Status change --}}
                <div class="border-top pt-3 mt-3">
                    <div class="text-muted small mb-2">Statusu Dəyiş</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(['pending' => 'warning', 'confirmed' => 'info', 'completed' => 'success', 'cancelled' => 'danger'] as $status => $color)
                            @if($appointment->status !== $status)
                                <form method="POST" action="{{ route('panel.appointments.update', $appointment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $status }}">
                                    <input type="hidden" name="patient_id" value="{{ $appointment->patient_id }}">
                                    <input type="hidden" name="scheduled_at" value="{{ $appointment->scheduled_at->format('Y-m-d\TH:i') }}">
                                    <input type="hidden" name="duration_minutes" value="{{ $appointment->duration_minutes }}">
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $color }}">
                                        {{ $labels[$status] }}
                                    </button>
                                </form>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Patient Info --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person me-2 text-success"></i>Müştəri Məlumatları
                </h6>
            </div>
            <div class="card-body">
                @php
                    $genderLabels = ['male' => 'Kişi', 'female' => 'Qadın', 'other' => 'Digər'];
                @endphp
                <div class="mb-2">
                    <a href="{{ route('panel.patients.show', $appointment->patient) }}" class="fw-semibold text-decoration-none fs-5">
                        {{ $appointment->patient->full_name }}
                    </a>
                </div>
                <div class="mb-2">
                    <span class="text-muted small">Telefon: </span>
                    <span class="fw-medium">{{ $appointment->patient->phone ?? '—' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted small">Doğum: </span>
                    <span class="fw-medium">
                        {{ $appointment->patient->birth_date ? $appointment->patient->birth_date->format('d.m.Y') : '—' }}
                    </span>
                </div>
                <div>
                    <span class="text-muted small">Cins: </span>
                    <span class="fw-medium">{{ $genderLabels[$appointment->patient->gender] ?? '—' }}</span>
                </div>
                @if($appointment->patient->notes)
                <div class="mt-2 p-2 bg-light rounded small text-muted">{{ $appointment->patient->notes }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- SMS Logs --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-chat-dots me-2 text-warning"></i>SMS Tarixçəsi
                </h6>
            </div>
            @php
                $typeLabels = [
                    'appointment_reminder'     => ['label' => 'Xatırlatma', 'color' => 'info'],
                    'appointment_confirmation' => ['label' => 'Təsdiq',     'color' => 'primary'],
                    'custom'                   => ['label' => 'Fərdi',      'color' => 'secondary'],
                ];
            @endphp

            {{-- Mobile SMS logs --}}
            <div class="d-md-none">
                @forelse($appointment->smsLogs ?? [] as $log)
                @php $typeData = $typeLabels[$log->type] ?? ['label' => $log->type, 'color' => 'secondary']; @endphp
                <div class="px-3 py-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="text-muted small">{{ $log->phone }}</span>
                        <div class="d-flex gap-1 ms-2">
                            <span class="badge bg-{{ $typeData['color'] }}">{{ $typeData['label'] }}</span>
                            @if($log->status === 'sent')
                                <span class="badge bg-success">Göndərildi</span>
                            @elseif($log->status === 'failed')
                                <span class="badge bg-danger">Uğursuz</span>
                            @else
                                <span class="badge bg-warning text-dark">Gözləyir</span>
                            @endif
                        </div>
                    </div>
                    <div class="small text-muted mt-1">{{ Str::limit($log->message, 80) }}</div>
                    <div class="text-muted mt-1" style="font-size:.75rem;">
                        {{ $log->sent_at ? $log->sent_at->format('d.m.Y H:i') : '—' }}
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-chat-x fs-3 d-block mb-2"></i>SMS loq yoxdur
                </div>
                @endforelse
            </div>

            {{-- Desktop SMS table --}}
            <div class="card-body p-0 d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Telefon</th>
                                <th>Mesaj</th>
                                <th>Növ</th>
                                <th>Status</th>
                                <th>Tarix</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointment->smsLogs ?? [] as $log)
                            @php $typeData = $typeLabels[$log->type] ?? ['label' => $log->type, 'color' => 'secondary']; @endphp
                            <tr>
                                <td class="text-muted small">{{ $log->phone }}</td>
                                <td>
                                    <span title="{{ $log->message }}" style="cursor:help;font-size:.8rem;">
                                        {{ Str::limit($log->message, 50) }}
                                    </span>
                                </td>
                                <td><span class="badge bg-{{ $typeData['color'] }}">{{ $typeData['label'] }}</span></td>
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
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-chat-x fs-3 d-block mb-2"></i>Bu randevu üçün SMS loq yoxdur
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
