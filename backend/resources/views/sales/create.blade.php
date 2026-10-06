@extends('layouts.desk')
@section('title', 'trama · nueva venta')
@section('desk-title', 'una venta nueva.')
@section('desk-stamp', 'precios del tarifario')
@section('desk-content')
@if ($errors->any())<div class="flash" role="alert"><p>revisa los datos de la venta.</p><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if ($customers->isEmpty() || $services->isEmpty())<p class="empty-state">registra un cliente y configura un servicio antes de emitir una venta.</p>@else
<form class="form-stack sale-form" method="post" action="{{ route('sales.store', ['branch' => $membership->branch_id]) }}">
@csrf<input type="hidden" name="request_key" value="{{ old('request_key', $requestKey) }}">
<div class="field"><label for="customer_id">cliente</label><select id="customer_id" name="customer_id" required><option value="">elige un cliente</option>@foreach ($customers as $customer)<option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>{{ $customer->name }} · {{ $customer->phone }}</option>@endforeach</select></div>
<p class="field-hint">el precio se toma del tarifario al emitir. para kilogramo puedes usar hasta tres decimales.</p>
<div id="sale-lines">@foreach (collect(is_array(old('lines')) ? old('lines') : [['service_id' => '', 'quantity' => '1']])->filter(fn ($line) => is_array($line))->values()->all() ?: [['service_id' => '', 'quantity' => '1']] as $index => $line)
<fieldset class="sale-line"><legend>servicio {{ $index + 1 }}</legend><div class="field"><label for="service-{{ $index }}">cuidado</label><select id="service-{{ $index }}" name="lines[{{ $index }}][service_id]" required><option value="">elige un servicio</option>@foreach ($services as $service)<option value="{{ $service->id }}" @selected((string) ($line['service_id'] ?? '') === (string) $service->id)>{{ $service->name }} · {{ $service->formattedPrice() }} / {{ $service->billing_unit->label() }}</option>@endforeach</select></div><div class="field"><label for="quantity-{{ $index }}">cantidad</label><input id="quantity-{{ $index }}" name="lines[{{ $index }}][quantity]" inputmode="decimal" value="{{ is_string($line['quantity'] ?? null) ? $line['quantity'] : '' }}" required maxlength="8"></div><button type="button" class="text-button remove-line">quitar servicio</button></fieldset>@endforeach</div>
<button type="button" class="button" id="add-line">añadir otro servicio</button>
<div class="field"><label for="notes">nota para el cliente</label><textarea id="notes" name="notes" maxlength="1000">{{ old('notes') }}</textarea></div>
<button type="submit" class="button primary">emitir comprobante →</button></form>
<script src="{{ asset('sales.js') }}" defer></script>
@endif
@endsection
