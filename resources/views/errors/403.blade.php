@extends('layouts.public')

@section('title', 'Giriş qadağandır | InnApp')
@section('meta_description', 'Bu səhifəyə baxmaq üçün icazəniz yoxdur.')
@section('robots', 'noindex, follow')

@section('content')
<div class="error-area default-padding text-center">
    <div class="container">
        <div class="error-code">403</div>
        <h1>Giriş qadağandır</h1>
        <p>Bu səhifəyə baxmaq üçün icazəniz yoxdur.</p>
        <a class="btn circle btn-theme border btn-md" href="{{ route('home') }}">Ana səhifəyə qayıt</a>
    </div>
</div>
@endsection
