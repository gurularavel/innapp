@extends('layouts.doctor')

@section('title', 'Yeni Xidmət Növü')
@section('page-title', 'Yeni Xidmət Növü')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Yeni Xidmət Növü Əlavə Et</h6>
                <a href="{{ route('panel.treatment-types.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('panel.treatment-types.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label fw-medium">Ad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label fw-medium">Qiymət (₼)</label>
                        <input type="number" step="0.01" min="0"
                               class="form-control @error('price') is-invalid @enderror"
                               id="price" name="price" value="{{ old('price') }}" placeholder="Boş = qiymət yoxdur">
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="duration_minutes" class="form-label fw-medium">Müddət (dəqiqə)</label>
                        <input type="number" min="5" step="5"
                               class="form-control @error('duration_minutes') is-invalid @enderror"
                               id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 30) }}">
                        @error('duration_minutes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="color" class="form-label fw-medium">Rəng (Təqvimdə)</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror"
                                   id="color" name="color" value="{{ old('color', '#3788d8') }}" style="width:60px;height:40px;">
                            <span class="text-muted small">Təqvimdə göstəriləcək rəng</span>
                        </div>
                        @error('color')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                        </button>
                        <a href="{{ route('panel.treatment-types.index') }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
