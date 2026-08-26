@extends('layouts.admin')

@section('title', 'Paketi Düzəlt')
@section('page-title', 'Paketi Düzəlt')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">{{ $package->name }} — Düzəliş</h6>
                <a href="{{ route('admin.packages.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.packages.update', $package) }}">
                    @csrf
                    @method('PATCH')

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label fw-medium">Paket Adı <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $package->name) }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="price_per_seat" class="form-label fw-medium">
                                Bir əməkdaş üçün qiymət (₼) <span class="text-danger">*</span>
                            </label>
                            <input type="number" step="0.01" min="0"
                                   class="form-control @error('price_per_seat') is-invalid @enderror"
                                   id="price_per_seat" name="price_per_seat" value="{{ old('price_per_seat', $package->price_per_seat) }}" required>
                            @error('price_per_seat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Aylıq məbləğ = bu qiymət × əməkdaş sayı.</div>
                        </div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="duration_days" class="form-label fw-medium">Müddət (gün) <span class="text-danger">*</span></label>
                            <input type="number" min="1"
                                   class="form-control @error('duration_days') is-invalid @enderror"
                                   id="duration_days" name="duration_days" value="{{ old('duration_days', $package->duration_days) }}" required>
                            @error('duration_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="patient_limit" class="form-label fw-medium">Müştəri Limiti</label>
                            <input type="number" min="0"
                                   class="form-control @error('patient_limit') is-invalid @enderror"
                                   id="patient_limit" name="patient_limit"
                                   value="{{ old('patient_limit', $package->patient_limit) }}"
                                   placeholder="Boş = limitsiz">
                            @error('patient_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Boş buraxsanız limitsiz olacaq.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="min_seats" class="form-label fw-medium">Minimum əməkdaş</label>
                            <input type="number" min="1"
                                   class="form-control @error('min_seats') is-invalid @enderror"
                                   id="min_seats" name="min_seats" value="{{ old('min_seats', $package->min_seats) }}">
                            @error('min_seats')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="max_seats" class="form-label fw-medium">Maksimum əməkdaş</label>
                            <input type="number" min="1"
                                   class="form-control @error('max_seats') is-invalid @enderror"
                                   id="max_seats" name="max_seats" value="{{ old('max_seats', $package->max_seats) }}"
                                   placeholder="Boş = limitsiz">
                            @error('max_seats')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Boş buraxsanız limitsiz olacaq.</div>
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-medium">Təsvir</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="2"
                                      placeholder="Paketin qısa izahı">{{ old('description', $package->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active"
                                       name="is_active" value="1"
                                       {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="is_active">Aktiv</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                        </button>
                        <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
