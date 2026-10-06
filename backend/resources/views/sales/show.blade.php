@extends('layouts.desk')
@section('title', 'trama · comprobante de venta')
@section('desk-title', $sale->number())
@section('desk-stamp', 'comprobante comercial')
@section('desk-content')
@if (session('status'))<p class="flash" role="status">{{ session('status') }}</p>@endif
<div class="split-desk"><section><p class="eyebrow">cliente</p><h2 class="panel-title">{{ $sale->customer_name }}</h2><p>{{ $sale->customer_phone }}</p><p class="count-note">{{ $sale->created_at->timezone($sale->timezone)->format('d/m/Y · H:i') }}</p>
@include('sales.lines')
<div class="sale-totals"><p>total <strong>{{ $sale::money($sale->total_minor) }} {{ $sale->currency }}</strong></p><p>pagado <strong>{{ $sale::money($paid) }}</strong></p><p>saldo <strong>{{ $sale::money($sale->total_minor - $paid) }}</strong></p></div>
@if ($sale->notes)<p class="sale-note">{{ $sale->notes }}</p>@endif
<form method="post" action="{{ route('sales.prepare-receipt', ['branch' => $membership->branch_id, 'sale' => $sale->id]) }}">@csrf<button class="button primary" type="submit">preparar recibo →</button></form>
<h2 class="panel-title history-title">historial.</h2><ol class="sale-history">@foreach ($sale->events as $event)<li><span class="record-number">{{ $event->created_at->timezone($sale->timezone)->format('d/m/Y · H:i') }}</span><p>{{ $event->details }} · {{ $event->actor->name }}</p></li>@endforeach</ol></section>
<section class="form-sheet"><p class="eyebrow">caja / {{ $sale->currency }}</p><h2 class="panel-title">registrar pago.</h2>
@if ($errors->any())<div role="alert" class="flash">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
@if ($paid < $sale->total_minor)<form class="form-stack" method="post" action="{{ route('sales.payment', ['branch' => $membership->branch_id, 'sale' => $sale->id]) }}">@csrf<input type="hidden" name="request_key" value="{{ old('request_key', $requestKey) }}"><div class="field"><label for="amount">importe</label><input id="amount" name="amount" inputmode="decimal" required value="{{ old('amount', $sale::money($sale->total_minor - $paid)) }}"></div><div class="field"><label for="method">medio de pago</label><select id="method" name="method">@foreach (['cash' => 'efectivo', 'transfer' => 'transferencia', 'card' => 'tarjeta'] as $value => $label)<option value="{{ $value }}" @selected(old('method') === $value)>{{ $label }}</option>@endforeach</select></div><div class="field"><label for="reference">referencia / opcional</label><input id="reference" name="reference" maxlength="120" value="{{ old('reference') }}"></div><p class="field-hint">registra un pago recibido. este formulario no procesa tarjetas ni transferencias.</p><button type="submit" class="button primary">guardar pago →</button></form>@else<p class="empty-state">venta pagada.</p>@endif
@foreach ($sale->payments as $payment)<div class="payment-entry"><strong>{{ $sale::money($payment->amount_minor) }} {{ $sale->currency }}</strong><p>{{ ['cash' => 'efectivo', 'transfer' => 'transferencia', 'card' => 'tarjeta'][$payment->method] }} · {{ $payment->reference }}</p></div>@endforeach
</section></div>
@endsection
