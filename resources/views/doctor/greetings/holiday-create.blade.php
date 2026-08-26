@extends('layouts.doctor')

@section('title', 'Yeni Tarix')
@section('page-title', 'Yeni Tarix')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-calendar-heart me-2 text-success"></i>Yeni Tarix</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('panel.greetings.holidays.store') }}">
                    @csrf

                    @include('holidays._fields')

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                        </button>
                        <a href="{{ route('panel.greetings.index') }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('holidays._scripts')
@endpush
