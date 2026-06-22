@extends('layouts.admin')

@section('title', 'Admin Düzəlişi')
@section('page-title', 'Admin Düzəlişi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">{{ $admin->full_name }}</h6>
                <a href="{{ route('admin.admins.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.admins.update', $admin) }}">
                    @method('PUT')
                    @include('admin.admins._form')
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Yadda Saxla</button>
                        <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
