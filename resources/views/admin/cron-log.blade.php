@extends('layouts.admin')

@section('title', 'Cron / SMS Diaqnostika')
@section('page-title', 'Cron / SMS Diaqnostika')

@section('content')
<div class="row g-4">

    {{-- Status Cards --}}
    <div class="col-12">
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 {{ $smsDriver === 'postaGuvercini' ? 'bg-success bg-opacity-10' : 'bg-warning bg-opacity-10' }}">
                            <i class="bi bi-chat-dots fs-4 {{ $smsDriver === 'postaGuvercini' ? 'text-success' : 'text-warning' }}"></i>
                        </div>
                        <div>
                            <div class="text-muted small">SMS Driver</div>
                            <div class="fw-semibold">{{ $smsDriver }}</div>
                            @if($smsDriver !== 'postaGuvercini')
                                <div class="text-warning small"><i class="bi bi-exclamation-triangle me-1"></i>Real SMS göndərilmir!</div>
                            @else
                                <div class="text-success small"><i class="bi bi-check-circle me-1"></i>Aktiv</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 bg-primary bg-opacity-10">
                            <i class="bi bi-alarm fs-4 text-primary"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Xatırlatma vaxtı</div>
                            <div class="fw-semibold">{{ $reminderMinutes }} dəq əvvəl</div>
                            <div class="text-muted small">Pəncərə: ±10 dəq</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 bg-info bg-opacity-10">
                            <i class="bi bi-clock fs-4 text-info"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Server vaxtı</div>
                            <div class="fw-semibold">{{ now()->format('H:i:s') }}</div>
                            <div class="text-muted small">{{ now()->format('d.m.Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 {{ file_exists(storage_path('logs/cron.log')) ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10' }}">
                            <i class="bi bi-file-text fs-4 {{ file_exists(storage_path('logs/cron.log')) ? 'text-success' : 'text-danger' }}"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Cron Log</div>
                            @if(file_exists(storage_path('logs/cron.log')))
                                <div class="fw-semibold">Var</div>
                                <div class="text-muted small">{{ round(filesize(storage_path('logs/cron.log')) / 1024, 1) }} KB</div>
                            @else
                                <div class="fw-semibold text-danger">Yoxdur</div>
                                <div class="text-muted small">Cron hələ işləməyib</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Upcoming appointments --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-calendar-event me-2 text-primary"></i>Növbəti Randevular (xatırlatma göndərilməmiş)</h6>
            </div>
            <div class="card-body p-0">
                @if($nextAppointments->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-calendar-x fs-2 mb-2 d-block"></i>
                        Gözləyən randevu yoxdur
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Tarix / Saat</th>
                                    <th>Pasiyent</th>
                                    <th>SMS vaxtı</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nextAppointments as $a)
                                <tr>
                                    <td class="text-muted small">{{ $a->id }}</td>
                                    <td class="fw-medium small">{{ $a->scheduled_at->format('d.m H:i') }}</td>
                                    <td class="small">{{ $a->patient->full_name }}</td>
                                    <td class="small text-primary">
                                        {{ $a->scheduled_at->subMinutes($reminderMinutes)->format('d.m H:i') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Cron log --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-terminal me-2 text-secondary"></i>Cron Log <span class="text-muted fw-normal small">(son 200 sətir)</span></h6>
                <a href="{{ route('admin.cron-log') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i>Yenilə
                </a>
            </div>
            <div class="card-body p-0">
                @if(empty($lines))
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-exclamation-circle fs-2 mb-2 d-block text-warning"></i>
                        <strong>Cron log boşdur</strong>
                        <p class="small mt-2 mb-0">
                            Bu o deməkdir ki <code>reminders:send</code> hələ heç vaxt işləməyib.<br>
                            Aşağıdakı əmri server-də yoxlayın:
                        </p>
                        <code class="small d-block mt-2 p-2 bg-light rounded text-start">* * * * * php {{ base_path() }}/artisan schedule:run >> /dev/null 2>&1</code>
                    </div>
                @else
                    <pre class="m-0 p-3 small" style="background:#1e1e2e; color:#cdd6f4; max-height:450px; overflow-y:auto; font-size:0.75rem; white-space:pre-wrap; word-break:break-all;">@foreach($lines as $line)@if(str_contains($line, 'FAILED') || str_contains($line, 'ERROR') || str_contains($line, 'error'))<span style="color:#f38ba8;">{{ $line }}</span>
@elseif(str_contains($line, 'sent') || str_contains($line, 'finished') || str_contains($line, 'Sent'))
<span style="color:#a6e3a1;">{{ $line }}</span>
@elseif(str_contains($line, 'started') || str_contains($line, 'found'))
<span style="color:#89b4fa;">{{ $line }}</span>
@else{{ $line }}
@endif@endforeach</pre>
                @endif
            </div>
        </div>
    </div>

    {{-- How-to instructions --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm border-start border-4 border-info">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-lightbulb me-2 text-info"></i>Test və Diaqnostika</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="small fw-medium mb-1">1. Driver-i yoxla (.env):</p>
                        <code class="small d-block p-2 bg-light rounded">SMS_DRIVER=postaGuvercini</code>
                    </div>
                    <div class="col-md-6">
                        <p class="small fw-medium mb-1">2. Cron-u server-də yoxla:</p>
                        <code class="small d-block p-2 bg-light rounded">crontab -l</code>
                    </div>
                    <div class="col-md-6">
                        <p class="small fw-medium mb-1">3. Test SMS göndər (telefon nömrəsinə):</p>
                        <code class="small d-block p-2 bg-light rounded">php artisan sms:test --phone=0551234567</code>
                    </div>
                    <div class="col-md-6">
                        <p class="small fw-medium mb-1">4. Konkret randevu üçün test et:</p>
                        <code class="small d-block p-2 bg-light rounded">php artisan sms:test --appointment=5</code>
                    </div>
                    <div class="col-md-6">
                        <p class="small fw-medium mb-1">5. Xatırlatma komandanı əl ilə işlət:</p>
                        <code class="small d-block p-2 bg-light rounded">php artisan reminders:send</code>
                    </div>
                    <div class="col-md-6">
                        <p class="small fw-medium mb-1">6. Cron schedule yoxla:</p>
                        <code class="small d-block p-2 bg-light rounded">php artisan schedule:list</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
