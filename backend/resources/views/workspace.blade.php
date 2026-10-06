@extends('layouts.desk')
@section('desk-content')
            <section class="welcome-sheet" aria-labelledby="welcome-title">
                <div class="sheet-marker" aria-hidden="true">01<span>inicio</span></div>
                <div><p class="eyebrow">primero, preparar la casa</p><h2 id="welcome-title">un buen registro<br>empieza antes del lavado.</h2><p class="muted">tu negocio y tu sucursal ya están conectados. la recepción de órdenes se habilitará en una siguiente etapa.</p></div>
            </section>
            <div class="setup-lines"><div><span>01</span><strong>negocio y sucursal</strong><span class="status-mark">configurados</span></div><div><span>02</span><strong>acceso del equipo</strong><span class="status-mark">{{ $membership->role->label() }}</span></div><div><span>03</span><strong>recepción de órdenes</strong><span class="muted">en construcción</span></div></div>
@endsection
