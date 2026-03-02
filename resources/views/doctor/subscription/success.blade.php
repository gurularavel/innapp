@extends('layouts.doctor')

@section('title', 'Abunəlik Aktivləşdi')
@section('page-title', 'Abunəlik Aktivləşdi')

@push('styles')
<style>
    @keyframes pop-in {
        0%   { transform: scale(0) rotate(-15deg); opacity: 0; }
        70%  { transform: scale(1.12) rotate(3deg); opacity: 1; }
        100% { transform: scale(1) rotate(0deg); }
    }
    @keyframes fade-up {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .success-icon { animation: pop-in .5s cubic-bezier(.36,.07,.19,.97) both; }
    .success-body { animation: fade-up .5s ease .3s both; }
    .detail-row { border-bottom: 1px solid #f1f3f5; padding: .6rem 0; }
    .detail-row:last-child { border-bottom: none; }
    .confetti-bar {
        height: 6px;
        background: linear-gradient(90deg, #3788d8, #198754, #ffc107, #dc3545, #3788d8);
        background-size: 300% 100%;
        animation: slide 3s linear infinite;
    }
    @keyframes slide { from { background-position: 0 } to { background-position: 300% 0 } }
</style>
@endpush

@section('content')

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">

        <div class="card border-0 shadow-sm overflow-hidden">
            {{-- Animated top bar --}}
            <div class="confetti-bar"></div>

            <div class="card-body p-5 text-center">

                {{-- Checkmark --}}
                <div class="success-icon mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success"
                         style="width:80px;height:80px">
                        <i class="bi bi-check-lg text-white" style="font-size:2.5rem"></i>
                    </div>
                </div>

                <div class="success-body">
                    <h4 class="fw-bold mb-1">Abunəliyiniz aktivdir!</h4>
                    <p class="text-muted mb-4">
                        Ödənişiniz qeydə alındı. Aşağıda abunəlik məlumatlarınızı görə bilərsiniz.
                    </p>

                    {{-- Details --}}
                    <div class="rounded-3 bg-light p-3 text-start mb-4">
                        <div class="detail-row d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Paket</span>
                            <span class="fw-semibold">{{ session('sub_package') }}</span>
                        </div>
                        <div class="detail-row d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Dövr</span>
                            <span class="fw-semibold">{{ session('sub_period') }}</span>
                        </div>
                        <div class="detail-row d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Məbləğ</span>
                            <span class="fw-semibold text-success">{{ number_format(session('sub_price'), 2) }} ₼</span>
                        </div>
                        <div class="detail-row d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Başlanğıc</span>
                            <span class="fw-semibold">{{ session('sub_starts') }}</span>
                        </div>
                        <div class="detail-row d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Bitmə tarixi</span>
                            <span class="fw-semibold">{{ session('sub_expires') }}</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('doctor.subscription.index') }}" class="btn btn-primary">
                            <i class="bi bi-shield-check me-2"></i>Abunəliyimə Bax
                        </a>
                        <a href="{{ route('doctor.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard-a Keç
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
