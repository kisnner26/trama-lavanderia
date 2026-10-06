@extends('layouts.app')
@section('title', 'trama · '.trim($__env->yieldContent('error-code')))
@section('body-class', 'error-page')
@section('content')
<header class="error-masthead"><a class="wordmark" href="{{ route('home') }}">trama<span class="brand-seam" aria-hidden="true"></span></a><span class="eyebrow">el oficio, en orden</span></header>
<main id="main" class="error-sheet">
    <p class="error-number" aria-hidden="true">@yield('error-code')</p>
    <div><p class="eyebrow">una pausa en el mostrador / @yield('error-code')</p><h1>@yield('error-heading')</h1><p class="muted">@yield('error-message')</p><a class="button primary" href="{{ route('home') }}">volver al inicio <span aria-hidden="true">→</span></a></div>
</main>
@endsection
