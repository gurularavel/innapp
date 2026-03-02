@extends('layouts.admin')

@section('title', 'Yeni Abunəlik')
@section('page-title', 'Yeni Abunəlik')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Yeni Abunəlik Əlavə Et</h6>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.subscriptions.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="doctor_id" class="form-label fw-medium">İstifadəçi <span class="text-danger">*</span></label>
                        <select class="form-select @error('doctor_id') is-invalid @enderror"
                                id="doctor_id" name="doctor_id" required>
                            <option value="">— İstifadəçi Seçin —</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}"
                                    {{ old('doctor_id', request('doctor_id')) == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->full_name }} ({{ $doctor->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="package_id" class="form-label fw-medium">Paket <span class="text-danger">*</span></label>
                        <select class="form-select @error('package_id') is-invalid @enderror"
                                id="package_id" name="package_id" required>
                            <option value="">— Paket Seçin —</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} — {{ number_format($package->price, 2) }} ₼
                                    ({{ $package->duration_days }} gün)
                                </option>
                            @endforeach
                        </select>
                        @error('package_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="starts_at" class="form-label fw-medium">Başlanğıc Tarixi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('starts_at') is-invalid @enderror"
                               id="starts_at" name="starts_at"
                               value="{{ old('starts_at', now()->format('Y-m-d')) }}" required>
                        @error('starts_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Abunəlik Ver
                        </button>
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
