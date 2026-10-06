@extends('layouts.app')
@section('title', 'trama · recibo '.$sale->number())
@section('content')
<nav class="receipt-controls" aria-label="acciones del recibo"><a class="button" href="{{ route('sales.show', ['branch' => $branch, 'sale' => $sale->id]) }}">volver a la venta</a><button class="button primary" type="button" id="print-receipt">imprimir recibo</button><label for="receipt-size">papel</label><select id="receipt-size"><option value="a4">a4 / carta</option><option value="thermal">térmico 80 mm</option></select></nav>
<main id="main" class="receipt"><header><p class="eyebrow">{{ $sale->branch_name }}</p><h1>{{ $sale->business_name }}</h1><p>comprobante de venta / {{ $sale->number() }}</p><p>{{ $sale->created_at->timezone($sale->timezone)->format('d/m/Y · H:i') }}</p></header><section><p class="eyebrow">cliente</p><h2>{{ $sale->customer_name }}</h2><p>{{ $sale->customer_phone }}</p></section>
@include('sales.lines')
<div class="sale-totals"><p>total <strong>{{ $sale::money($sale->total_minor) }} {{ $sale->currency }}</strong></p><p>pagado <strong>{{ $sale::money($paid) }}</strong></p><p>saldo pendiente <strong>{{ $sale::money($sale->total_minor - $paid) }} {{ $sale->currency }}</strong></p></div>
@if ($sale->payments->isNotEmpty())<section><p class="eyebrow">pagos recibidos</p>@foreach ($sale->payments as $payment)<p>{{ $payment->created_at->timezone($sale->timezone)->format('d/m/Y H:i') }} · {{ ['cash' => 'efectivo', 'transfer' => 'transferencia', 'card' => 'tarjeta'][$payment->method] }} · {{ $sale::money($payment->amount_minor) }} {{ $sale->currency }}@if ($payment->reference) · {{ $payment->reference }}@endif</p>@endforeach</section>@endif
@if ($sale->notes)<p class="sale-note">{{ $sale->notes }}</p>@endif
<footer><p>gracias por confiar en nuestro cuidado.</p><small>comprobante comercial · no es factura fiscal</small></footer></main>
<script src="{{ asset('sales.js') }}" defer></script>
@endsection
