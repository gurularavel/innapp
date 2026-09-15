@extends('layouts.public')

@section('title', 'Səhifə tapılmadı | InnApp')
@section('meta_description', 'Axtardığınız səhifə silinib və ya ünvan səhvdir.')
@section('robots', 'noindex, follow')

@section('content')
<div class="error-area default-padding text-center">
    <div class="container">
        <div class="error-code">404</div>
        <h1>Səhifə tapılmadı</h1>
        <p>Axtardığınız səhifə silinib və ya ünvan səhvdir.</p>
        <a class="btn circle btn-theme border btn-md" href="{{ route('home') }}">Ana səhifəyə qayıt</a>
    </div>
</div>
@endsection
