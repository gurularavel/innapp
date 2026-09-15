@extends('layouts.public')

@section('title', 'Texniki işlər aparılır | InnApp')
@section('meta_description', 'Sistem qısa müddətə əlçatmazdır. Zəhmət olmasa bir az sonra yenidən daxil olun.')
@section('robots', 'noindex, follow')

@section('content')
<div class="error-area default-padding text-center">
    <div class="container">
        <div class="error-code">503</div>
        <h1>Texniki işlər aparılır</h1>
        <p>Sistem qısa müddətə əlçatmazdır. Zəhmət olmasa bir az sonra yenidən daxil olun.</p>
        <a class="btn circle btn-theme border btn-md" href="{{ route('home') }}">Ana səhifəyə qayıt</a>
    </div>
</div>
@endsection
