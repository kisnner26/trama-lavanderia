@extends('layouts.desk')
@section('title', 'trama · registro de clientes')
@section('desk-title', 'el registro.')
@section('desk-kicker', 'clientes / '.$membership->branch->name)
@section('desk-stamp', 'datos del negocio')
@section('desk-content')
@if (session('status'))<p class="flash" role="status">{{ session('status') }}</p>@endif
<div class="split-desk">
    <section aria-labelledby="customers-title">
        <h2 class="panel-title" id="customers-title">cada nombre, una ficha.</h2>
        <form method="get" action="{{ route('customers.index', ['branch' => $membership->branch_id]) }}" class="search-form">
            <div class="field"><label for="q">buscar por nombre o teléfono</label><input id="q" name="q" value="{{ $search }}" maxlength="80" type="search" @error('q') aria-invalid="true" aria-describedby="q-error" @enderror>@error('q')<p id="q-error" class="field-error" role="alert">{{ $message }}</p>@enderror</div>
            <button type="submit" class="button secondary">buscar</button>
        </form>
        <p class="count-note">{{ $customers->total() }} {{ $customers->total() === 1 ? 'ficha' : 'fichas' }}{{ $search !== '' ? ' en esta búsqueda' : ' en el registro' }}</p>
        <div class="record-list">
        @forelse ($customers as $customer)
            <article class="record-row"><div><div class="record-number">ficha / {{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</div><strong>{{ $customer->name }}</strong>@if ($customer->phone)<p>{{ $customer->phone }}</p>@endif @if ($customer->notes)<p class="record-note">{{ $customer->notes }}</p>@endif</div></article>
        @empty
            <p class="empty-state">{{ $search !== '' ? 'no encontramos fichas con esa búsqueda.' : 'todavía no hay clientes. registra el primero cuando llegue al mostrador.' }}</p>
        @endforelse
        </div>
        @include('partials.pagination', ['records' => $customers])
    </section>
    <section class="form-sheet" aria-labelledby="new-customer-title">
        <p class="eyebrow">alta / cliente</p><h2 id="new-customer-title" class="panel-title">una ficha nueva.</h2>
        <form method="post" action="{{ route('customers.store', ['branch' => $membership->branch_id]) }}" class="form-stack">
            @csrf
            <div class="field"><label for="name">nombre del cliente</label><input id="name" name="name" autocomplete="name" value="{{ old('name') }}" required maxlength="120" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>@error('name')<p id="name-error" class="field-error" role="alert">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="phone">teléfono <span class="muted">/ opcional</span></label><input id="phone" name="phone" type="tel" autocomplete="tel" value="{{ old('phone') }}" maxlength="30" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>@error('phone')<p id="phone-error" class="field-error" role="alert">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="notes">nota <span class="muted">/ opcional</span></label><textarea id="notes" name="notes" rows="3" maxlength="1000" @error('notes') aria-invalid="true" aria-describedby="notes-error" @enderror>{{ old('notes') }}</textarea>@error('notes')<p id="notes-error" class="field-error" role="alert">{{ $message }}</p>@enderror</div>
            <p class="field-hint">anota solo información necesaria para atender al cliente.</p>
            <button type="submit" class="button primary">guardar ficha <span aria-hidden="true">→</span></button>
        </form>
    </section>
</div>
@endsection
