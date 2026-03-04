@extends('layouts.doctor')

@section('title', 'Yeni Müştəri')
@section('page-title', 'Yeni Müştəri')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Yeni Müştəri Əlavə Et</h6>
                <a href="{{ route('panel.patients.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('panel.patients.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-medium">Ad <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="surname" class="form-label fw-medium">Soyad <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                   id="surname" name="surname" value="{{ old('surname') }}" required>
                            @error('surname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-medium">Telefon</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone') }}" >
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="birth_date" class="form-label fw-medium">Doğum Tarixi</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                                   id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                            @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="gender" class="form-label fw-medium">Cins</label>
                            <select class="form-select @error('gender') is-invalid @enderror"
                                    id="gender" name="gender">
                                <option value="">— Seçin —</option>
                                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Kişi</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Qadın</option>
                                <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Digər</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="weight" class="form-label fw-medium">Çəki (kg)</label>
                            <input type="number" min="0" max="999" step="0.1"
                                   class="form-control @error('weight') is-invalid @enderror"
                                   id="weight" name="weight" value="{{ old('weight') }}" placeholder="72.5">
                            @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="blood_type" class="form-label fw-medium">Qan Qrupu</label>
                            <select class="form-select @error('blood_type') is-invalid @enderror"
                                    id="blood_type" name="blood_type">
                                <option value="">— Seçin —</option>
                                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                                    <option value="{{ $bt }}" {{ old('blood_type') === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                @endforeach
                            </select>
                            @error('blood_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="marital_status" class="form-label fw-medium">Ailə Vəziyyəti</label>
                            <select class="form-select @error('marital_status') is-invalid @enderror"
                                    id="marital_status" name="marital_status">
                                <option value="">— Seçin —</option>
                                <option value="single"   {{ old('marital_status') === 'single'   ? 'selected' : '' }}>Subay</option>
                                <option value="married"  {{ old('marital_status') === 'married'  ? 'selected' : '' }}>Evli</option>
                                <option value="divorced" {{ old('marital_status') === 'divorced' ? 'selected' : '' }}>Boşanmış</option>
                                <option value="widowed"  {{ old('marital_status') === 'widowed'  ? 'selected' : '' }}>Dul</option>
                            </select>
                            @error('marital_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label fw-medium">Qeydlər</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="4"
                                      placeholder="Müştəri haqqında əlavə məlumat...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                        </button>
                        <a href="{{ route('panel.patients.index') }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
