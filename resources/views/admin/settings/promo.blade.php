@extends('layouts.admin')

@section('title', 'Promotor Ayarları')
@section('page-title', 'Promotor Ayarları')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-ticket-perforated me-2 text-primary"></i>Defolt Promo Kod Faizləri</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Promotor <strong>özü qeydiyyatdan keçdikdə</strong> ona avtomatik yaradılan promo kodun defolt faizləri.
                    Mövcud kodların faizlərini fərdi dəyişmək üçün
                    <a href="{{ route('admin.promo-codes.index') }}">Promo Kodlar</a> bölməsindən istifadə edin —
                    bu ayar artıq yaradılmış kodlara təsir etmir.
                </p>

                <form method="POST" action="{{ route('admin.settings.promo.save') }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="promo_default_discount_percent" class="form-label fw-medium">
                                İlkin qeydiyyat endirimi (%)
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100"
                                       id="promo_default_discount_percent"
                                       name="promo_default_discount_percent"
                                       class="form-control @error('promo_default_discount_percent') is-invalid @enderror"
                                       value="{{ old('promo_default_discount_percent', $discountPercent) }}"
                                       required>
                                <span class="input-group-text">%</span>
                                @error('promo_default_discount_percent')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Promo kodla qeydiyyatdan keçən müştərinin ilk ödənişinə tətbiq olunan endirim.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="promo_default_commission_percent" class="form-label fw-medium">
                                Ödəniş komissiyası (%)
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100"
                                       id="promo_default_commission_percent"
                                       name="promo_default_commission_percent"
                                       class="form-control @error('promo_default_commission_percent') is-invalid @enderror"
                                       value="{{ old('promo_default_commission_percent', $commissionPercent) }}"
                                       required>
                                <span class="input-group-text">%</span>
                                @error('promo_default_commission_percent')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Müştərinin hər uğurlu ödənişindən promotora yazılan komissiya.</div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
