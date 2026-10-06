@extends('layouts.app')
@section('content')
<div class="workspace-shell">
    <header class="topbar">
        <a class="wordmark" href="{{ route('home') }}">trama<span class="brand-seam" aria-hidden="true"></span></a>
        <span class="topbar-purpose">el oficio, en orden</span>
        <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="text-button">cerrar sesión</button></form>
    </header>
    <div class="workspace-grid">
        <aside class="sidebar" aria-label="contexto del equipo">
            <p class="eyebrow">tu espacio</p>
            <h2 class="business-name">{{ $membership->branch->business->name }}</h2>
            <p class="branch-name">{{ $membership->branch->name }}</p>
            <nav class="branch-nav" aria-label="sucursales asignadas">
                @foreach ($memberships as $assigned)
                    <a href="{{ route('workspace', ['branch' => $assigned->branch_id]) }}" @if ($assigned->branch_id === $membership->branch_id) aria-current="page" @endif><span>{{ $assigned->branch->name }}</span><span aria-hidden="true">↗</span></a>
                @endforeach
            </nav>
            <div class="team-signature"><span class="eyebrow">sesión del equipo</span><strong>{{ auth()->user()->name }}</strong><span>{{ $membership->role->label() }}</span></div>
        </aside>
        <main id="main" class="desk">
            <div class="desk-heading"><div><p class="eyebrow">recepción / {{ $membership->branch->name }}</p><h1>el mostrador.</h1></div><span class="outline-stamp">base del negocio</span></div>
            <nav class="desk-tabs" aria-label="secciones"><a href="{{ route('workspace', ['branch' => $membership->branch_id]) }}" aria-current="page">inicio</a></nav>
            <section class="welcome-sheet" aria-labelledby="welcome-title">
                <div class="sheet-marker" aria-hidden="true">01<span>inicio</span></div>
                <div><p class="eyebrow">primero, preparar la casa</p><h2 id="welcome-title">un buen registro<br>empieza antes del lavado.</h2><p class="muted">tu negocio y tu sucursal ya están conectados. la recepción de órdenes se habilitará en una siguiente etapa.</p></div>
            </section>
            <div class="setup-lines"><div><span>01</span><strong>negocio y sucursal</strong><span class="status-mark">configurados</span></div><div><span>02</span><strong>acceso del equipo</strong><span class="status-mark">{{ $membership->role->label() }}</span></div><div><span>03</span><strong>recepción de órdenes</strong><span class="muted">en construcción</span></div></div>
            <footer class="desk-footer"><span>trama / {{ $membership->branch->business->currency }}</span><span>{{ $membership->branch->business->timezone }}</span></footer>
        </main>
    </div>
</div>
@endsection
