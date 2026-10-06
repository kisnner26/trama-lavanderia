@extends('layouts.app')
@section('title', 'trama · abrir el mostrador')
@section('body-class', 'access-page')
@section('content')
<div class="access-shell">
    <section class="access-story" aria-labelledby="brand-title">
        <a class="wordmark" href="{{ route('login') }}" aria-label="trama, acceso">trama<span class="brand-seam" aria-hidden="true"></span></a>
        <p class="eyebrow">el oficio, en orden / 01</p>
        <h1 id="brand-title">cada pieza.<br><span>en su sitio.</span></h1>
        <div class="sewn-label" aria-hidden="true"><span>trama / registro de cuidado</span><strong>t</strong><div class="label-stitches"></div><span>recepción · proceso · entrega</span></div>
        <p class="story-caption">el trabajo empieza aquí.<br>la tranquilidad llega a la entrega.</p>
        <p class="story-foot">sistema de gestión para lavanderías</p>
    </section>
    <main id="main" class="access-form">
        <p class="eyebrow">acceso del equipo</p>
        <h2>abrir el<br>mostrador.</h2>
        <p class="muted">entra con la cuenta asignada a tu sucursal.</p>
        <form action="{{ route('login.store') }}" method="post" class="form-stack">
            @csrf
            <div class="field">
                <label for="email">correo</label>
                <input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" required maxlength="254" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')<p id="email-error" class="field-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="password">contraseña</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required maxlength="128" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                @error('password')<p id="password-error" class="field-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <button class="button primary" type="submit">entrar al mostrador <span aria-hidden="true">→</span></button>
        </form>
        <p class="access-help">¿no tienes acceso? solicita una cuenta al responsable del negocio.</p>
        <span class="small-stamp">trama / trabajo bien cuidado</span>
    </main>
</div>
@endsection
