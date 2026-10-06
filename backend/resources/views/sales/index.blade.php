@extends('layouts.desk')
@section('title', 'trama · ventas')
@section('desk-title', 'el libro de ventas.')
@section('desk-stamp', 'comprobantes comerciales')
@section('desk-content')
<div class="sale-toolbar"><p class="count-note">{{ $sales->total() }} ventas en esta sucursal</p><a class="button primary" href="{{ route('sales.create', ['branch' => $membership->branch_id]) }}">emitir venta →</a></div>
<form method="get" class="search-form"><div class="field"><label for="q">buscar cliente o número de venta</label><input id="q" name="q" value="{{ $search }}" maxlength="80"></div><button class="button" type="submit">buscar</button></form>
<div class="record-list">@forelse ($sales as $sale)
<article class="record-row"><div><a href="{{ route('sales.show', ['branch' => $membership->branch_id, 'sale' => $sale->id]) }}"><span class="record-number">{{ $sale->number() }}</span><strong>{{ $sale->customer_name }}</strong></a><p>{{ $sale->created_at->timezone($sale->timezone)->format('d/m/Y · H:i') }}</p></div><div class="service-price"><strong>{{ $sale::money($sale->total_minor) }} {{ $sale->currency }}</strong><span>saldo {{ $sale::money($sale->total_minor - (int) $sale->payments_sum_amount_minor) }}</span></div></article>
@empty<p class="empty-state">el libro está listo para la primera venta.</p>@endforelse</div>
@include('partials.pagination', ['records' => $sales])
@endsection
