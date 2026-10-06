@extends('layouts.desk')
@section('title', 'trama · tarifario de servicios')
@section('desk-title', 'el tarifario.')
@section('desk-kicker', 'servicios / '.$membership->branch->name)
@section('desk-stamp', $membership->branch->business->currency.' / precios del negocio')
@section('desk-content')
@if (session('status'))<p class="flash" role="status">{{ session('status') }}</p>@endif
<div class="@can('manage-catalog', $membership) split-desk @endcan">
    <section aria-labelledby="services-title">
        <h2 class="panel-title" id="services-title">cada cuidado, su precio.</h2>
        @can('manage-catalog', $membership)<a class="mobile-create" href="#new-service-title">añadir servicio →</a>@endcan
        <p class="count-note">{{ $services->total() }} {{ $services->total() === 1 ? 'servicio configurado' : 'servicios configurados' }}</p>
        <div class="record-list">
        @forelse ($services as $service)
            <article class="record-row"><div><div class="record-number">servicio / {{ str_pad($service->id, 4, '0', STR_PAD_LEFT) }}</div><strong>{{ $service->name }}</strong><p>{{ $service->billing_unit->label() }} · {{ $service->requires_finish ? 'incluye acabado' : 'sin acabado' }}</p></div><div class="service-price"><strong class="price-tag">{{ $service->formattedPrice() }}</strong><span class="record-number">{{ $membership->branch->business->currency }}</span></div></article>
        @empty
            <p class="empty-state">todavía no hay servicios. el propietario puede configurar el tarifario antes de recibir la primera orden.</p>
        @endforelse
        </div>
        @include('partials.pagination', ['records' => $services])
    </section>
    @can('manage-catalog', $membership)
    <section class="form-sheet" aria-labelledby="new-service-title">
        <p class="eyebrow">alta / servicio</p><h2 id="new-service-title" class="panel-title">un cuidado nuevo.</h2>
        <form method="post" action="{{ route('services.store', ['branch' => $membership->branch_id]) }}" class="form-stack">
            @csrf
            <div class="field"><label for="name">nombre del servicio</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="120" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>@error('name')<p id="name-error" class="field-error" role="alert">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="billing_unit">forma de cobro</label><select id="billing_unit" name="billing_unit" required @error('billing_unit') aria-invalid="true" aria-describedby="unit-error" @enderror>@foreach ($billingUnits as $unit)<option value="{{ $unit->value }}" @selected(old('billing_unit', 'piece') === $unit->value)>{{ $unit->label() }}</option>@endforeach</select>@error('billing_unit')<p id="unit-error" class="field-error" role="alert">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="price">precio / {{ $membership->branch->business->currency }}</label><input id="price" name="price" type="text" inputmode="decimal" value="{{ old('price') }}" required maxlength="10" aria-describedby="price-hint @error('price') price-error @enderror" @error('price') aria-invalid="true" @enderror><p id="price-hint" class="field-hint">por la unidad elegida. usa punto para los decimales.</p>@error('price')<p id="price-error" class="field-error" role="alert">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="requires_finish">ruta de trabajo</label><select id="requires_finish" name="requires_finish" required @error('requires_finish') aria-invalid="true" aria-describedby="route-error" @enderror><option value="1" @selected((string) old('requires_finish', '1') === '1')>incluye acabado</option><option value="0" @selected((string) old('requires_finish') === '0')>sin acabado</option></select>@error('requires_finish')<p id="route-error" class="field-error" role="alert">{{ $message }}</p>@enderror</div>
            <button type="submit" class="button primary">guardar servicio <span aria-hidden="true">→</span></button>
        </form>
    </section>
    @endcan
</div>
@endsection
