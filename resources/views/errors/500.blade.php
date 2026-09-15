@extends('layouts.public')

@section('title', 'Server xətası | InnApp')
@section('meta_description', 'Gözlənilməz xəta baş verdi. Komandamız məlumatlandırıldı.')
@section('robots', 'noindex, follow')

@section('content')
<div class="error-area default-padding text-center">
    <div class="container">
        <div class="error-code">500</div>
        <h1>Server xətası</h1>
        <p>Gözlənilməz xəta baş verdi. Komandamız məlumatlandırıldı.</p>
        <a class="btn circle btn-theme border btn-md" href="{{ route('home') }}">Ana səhifəyə qayıt</a>
    </div>
</div>
@endsection
