@extends('layouts.admin')

@section('title', 'Promo Kod Düzəlişi')
@section('page-title', 'Promo Kod Düzəlişi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Promo Kod: {{ $promoCode->code }}</h6>
                <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.promo-codes.update', $promoCode) }}">
                    @method('PUT')
                    @include('admin.promo-codes._form')
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Yadda Saxla</button>
                        <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
