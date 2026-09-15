@extends('layouts.public')

@section('title', 'Çox sayda sorğu | InnApp')
@section('meta_description', 'Qısa müddətdə həddindən artıq sorğu göndərildi. Bir az sonra yenidən cəhd edin.')
@section('robots', 'noindex, follow')

@section('content')
<div class="error-area default-padding text-center">
    <div class="container">
        <div class="error-code">429</div>
        <h1>Çox sayda sorğu</h1>
        <p>Qısa müddətdə həddindən artıq sorğu göndərildi. Bir az sonra yenidən cəhd edin.</p>
        <a class="btn circle btn-theme border btn-md" href="{{ route('home') }}">Ana səhifəyə qayıt</a>
    </div>
</div>
@endsection
