@extends('layouts.public')

@section('title', 'Sessiyanın vaxtı bitib | InnApp')
@section('meta_description', 'Səhifə çox uzun müddət açıq qalıb. Zəhmət olmasa yenidən cəhd edin.')
@section('robots', 'noindex, follow')

@section('content')
<div class="error-area default-padding text-center">
    <div class="container">
        <div class="error-code">419</div>
        <h1>Sessiyanın vaxtı bitib</h1>
        <p>Səhifə çox uzun müddət açıq qalıb. Zəhmət olmasa yenidən cəhd edin.</p>
        <a class="btn circle btn-theme border btn-md" href="{{ route('home') }}">Ana səhifəyə qayıt</a>
    </div>
</div>
@endsection
