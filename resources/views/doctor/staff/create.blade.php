@extends('layouts.doctor')

@section('title', 'Yeni əməkdaş')
@section('page-title', 'Yeni əməkdaş')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-person-plus me-2 text-primary"></i>Yeni əməkdaş</h6>
                <span class="badge bg-light text-dark border">
                    {{ $clinic->remainingSeats() }} boş yer
                </span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Yeni hesab bir ödənişli yer tutur. Əməkdaş bu e-poçt və şifrə ilə panelə daxil ola biləcək.
                </p>

                <form method="POST" action="{{ route('panel.staff.store') }}">
                    @csrf
                    @include('doctor.staff._form')

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Əlavə et
                        </button>
                        <a href="{{ route('panel.staff.index') }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
