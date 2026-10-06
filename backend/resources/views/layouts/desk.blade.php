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
            <div class="desk-heading"><div><p class="eyebrow">@yield('desk-kicker', 'recepción / '.$membership->branch->name)</p><h1>@yield('desk-title', 'el mostrador.')</h1></div><span class="outline-stamp">@yield('desk-stamp', 'base del negocio')</span></div>
            <nav class="desk-tabs" aria-label="secciones">
                <a href="{{ route('workspace', ['branch' => $membership->branch_id]) }}" @if (request()->routeIs('workspace')) aria-current="page" @endif>inicio</a>
                @can('receive', $membership)<a href="{{ route('customers.index', ['branch' => $membership->branch_id]) }}" @if (request()->routeIs('customers.*')) aria-current="page" @endif>clientes</a>
                <a href="{{ route('services.index', ['branch' => $membership->branch_id]) }}" @if (request()->routeIs('services.*')) aria-current="page" @endif>servicios</a>@endcan
            </nav>
            @yield('desk-content')
            <footer class="desk-footer"><span>trama / {{ $membership->branch->business->currency }}</span><span>{{ $membership->branch->business->timezone }}</span></footer>
        </main>
    </div>
</div>
@endsection
