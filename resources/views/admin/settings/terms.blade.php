@extends('layouts.admin')

@section('title', 'İstifadə Qaydaları')
@section('page-title', 'İstifadə Qaydaları')

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
                <h6 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Qeydiyyat Qaydaları</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Bu mətn qeydiyyat səhifəsində <strong>"Qaydaları qəbul edirəm"</strong> bölməsindəki linkə basıldıqda
                    açılan pəncərədə göstərilir. İstifadəçi hesab yaratmaq üçün qaydaları qəbul etməlidir.
                </p>

                <form method="POST" action="{{ route('admin.settings.terms.save') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="terms_title" class="form-label fw-medium">Başlıq</label>
                        <input type="text"
                               id="terms_title"
                               name="terms_title"
                               class="form-control @error('terms_title') is-invalid @enderror"
                               value="{{ old('terms_title', $termsTitle) }}"
                               placeholder="İstifadə Qaydaları"
                               required>
                        @error('terms_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="terms_content" class="form-label fw-medium">Qaydaların mətni</label>
                        <textarea id="terms_content"
                                  name="terms_content"
                                  class="form-control @error('terms_content') is-invalid @enderror"
                                  rows="16"
                                  placeholder="İstifadə qaydalarını buraya yazın..."
                                  required>{{ old('terms_content', $termsContent) }}</textarea>
                        <div class="form-text">Sətir keçidləri (abzaslar) qorunur. Hər bənddən sonra Enter basın.</div>
                        @error('terms_content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
