@extends('layouts.doctor')

@section('title', 'Əməkdaş redaktəsi')
@section('page-title', 'Əməkdaş redaktəsi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-gear me-2 text-primary"></i>{{ $staff->full_name }}
                </h6>
                <span class="badge bg-{{ $staff->is_active ? 'success' : 'secondary' }}">
                    {{ $staff->is_active ? 'Aktiv' : 'Deaktiv' }}
                </span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('panel.staff.update', $staff) }}">
                    @csrf
                    @method('PUT')
                    @include('doctor.staff._form')

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda saxla
                        </button>
                        <a href="{{ route('panel.staff.index') }}" class="btn btn-outline-secondary">Geri</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
